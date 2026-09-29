//
//  APIClient.swift
//  RoadFix
//
//  Talks to the RoadFix REST API hosted on Railway (see /server).
//

import Foundation
import Security

enum APIConfig {
    // Set this to your Railway API service's public domain (Settings ->
    // Networking -> Generate Domain). HTTPS only; iOS blocks plain http.
    static let baseURL = URL(string: "https://api-production-45e31.up.railway.app")!
}

struct APIError: LocalizedError {
    let message: String
    let statusCode: Int?

    var errorDescription: String? { message }
    var isUnauthorized: Bool { statusCode == 401 }

    static let network = APIError(message: "Network error. Check your connection and try again.", statusCode: nil)
    static let unknown = APIError(message: "Something went wrong, try again.", statusCode: nil)
}

struct APIUser: Decodable {
    let id: String
    let email: String
    let role: String
}

struct AuthResponse: Decodable {
    let token: String
    let user: APIUser
}

private struct MeResponse: Decodable { let user: APIUser }
private struct ReportsResponse: Decodable { let reports: [Report] }
private struct ReportResponse: Decodable { let report: Report }
private struct ErrorResponse: Decodable { let error: String }

extension Notification.Name {
    // Posted when the server rejects our saved token, so the UI can sign out.
    static let apiSessionExpired = Notification.Name("apiSessionExpired")
}

final class APIClient {
    static let shared = APIClient()

    private let session = URLSession.shared
    private let decoder: JSONDecoder = {
        let decoder = JSONDecoder()
        decoder.dateDecodingStrategy = .custom { decoder in
            let text = try decoder.singleValueContainer().decode(String.self)
            let withFraction = ISO8601DateFormatter()
            withFraction.formatOptions = [.withInternetDateTime, .withFractionalSeconds]
            if let date = withFraction.date(from: text) { return date }
            if let date = ISO8601DateFormatter().date(from: text) { return date }
            throw DecodingError.dataCorrupted(
                .init(codingPath: decoder.codingPath, debugDescription: "Bad date: \(text)")
            )
        }
        return decoder
    }()

    var token: String? { Keychain.read(key: "authToken") }

    func setToken(_ token: String?) {
        if let token {
            Keychain.save(token, key: "authToken")
        } else {
            Keychain.delete(key: "authToken")
        }
    }

    // MARK: Auth

    func signIn(email: String, password: String) async throws -> APIUser {
        let response: AuthResponse = try await send(
            "POST", "/auth/login", json: ["email": email, "password": password]
        )
        setToken(response.token)
        return response.user
    }

    func signUp(email: String, password: String, staffCode: String?) async throws -> APIUser {
        var body = ["email": email, "password": password]
        if let staffCode, !staffCode.isEmpty { body["staffCode"] = staffCode }
        let response: AuthResponse = try await send("POST", "/auth/signup", json: body)
        setToken(response.token)
        return response.user
    }

    func me() async throws -> APIUser {
        let response: MeResponse = try await send("GET", "/me")
        return response.user
    }

    // MARK: Reports

    func fetchReports() async throws -> [Report] {
        let response: ReportsResponse = try await send("GET", "/reports")
        return response.reports.map(resolvingPhotoURL)
    }

    func createReport(
        category: ReportCategory,
        description: String,
        latitude: Double,
        longitude: Double,
        photoJPEG: Data?
    ) async throws -> Report {
        var form = MultipartForm()
        form.addField("category", category.rawValue)
        form.addField("description", description)
        form.addField("latitude", String(latitude))
        form.addField("longitude", String(longitude))
        if let photoJPEG { form.addFile("photo", filename: "photo.jpg", mimeType: "image/jpeg", data: photoJPEG) }
        let response: ReportResponse = try await send(
            "POST", "/reports", body: form.closedData, contentType: form.contentType
        )
        return resolvingPhotoURL(response.report)
    }

    func toggleUpvote(reportID: String) async throws -> Report {
        let response: ReportResponse = try await send("POST", "/reports/\(reportID)/upvote")
        return resolvingPhotoURL(response.report)
    }

    func updateStatus(reportID: String, status: ReportStatus) async throws -> Report {
        let response: ReportResponse = try await send(
            "PATCH", "/reports/\(reportID)/status", json: ["status": status.rawValue]
        )
        return resolvingPhotoURL(response.report)
    }

    // The server returns photo paths like "/photos/<id>.jpg"; the views want
    // a full URL for AsyncImage.
    private func resolvingPhotoURL(_ report: Report) -> Report {
        var report = report
        if let path = report.photoURL, path.hasPrefix("/") {
            report.photoURL = APIConfig.baseURL.absoluteString + path
        }
        return report
    }

    // MARK: Transport

    private func send<T: Decodable>(
        _ method: String,
        _ path: String,
        json: [String: String]? = nil,
        body: Data? = nil,
        contentType: String? = nil
    ) async throws -> T {
        var request = URLRequest(url: APIConfig.baseURL.appendingPathComponent(path))
        request.httpMethod = method
        request.timeoutInterval = 30
        let sentToken = token
        if let sentToken { request.setValue("Bearer \(sentToken)", forHTTPHeaderField: "Authorization") }
        if let json {
            request.httpBody = try JSONSerialization.data(withJSONObject: json)
            request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        } else if let body {
            request.httpBody = body
            request.setValue(contentType, forHTTPHeaderField: "Content-Type")
        }

        let data: Data
        let response: URLResponse
        do {
            (data, response) = try await session.data(for: request)
        } catch {
            throw APIError.network
        }
        guard let http = response as? HTTPURLResponse else { throw APIError.unknown }

        guard (200..<300).contains(http.statusCode) else {
            let message = (try? decoder.decode(ErrorResponse.self, from: data))?.error ?? APIError.unknown.message
            // A 401 while holding a token means the session is no longer valid.
            // (Login/signup 401s happen with no token, so they don't trigger this.)
            if http.statusCode == 401, sentToken != nil {
                NotificationCenter.default.post(name: .apiSessionExpired, object: nil)
            }
            throw APIError(message: message, statusCode: http.statusCode)
        }
        do {
            return try decoder.decode(T.self, from: data)
        } catch {
            print("Decode error for \(path): \(error)")
            throw APIError.unknown
        }
    }
}

// Minimal multipart/form-data builder for the photo upload.
private struct MultipartForm {
    private let boundary = "Boundary-\(UUID().uuidString)"
    private var data = Data()

    var contentType: String { "multipart/form-data; boundary=\(boundary)" }

    mutating func addField(_ name: String, _ value: String) {
        append("--\(boundary)\r\n")
        append("Content-Disposition: form-data; name=\"\(name)\"\r\n\r\n")
        append("\(value)\r\n")
    }

    mutating func addFile(_ name: String, filename: String, mimeType: String, data fileData: Data) {
        append("--\(boundary)\r\n")
        append("Content-Disposition: form-data; name=\"\(name)\"; filename=\"\(filename)\"\r\n")
        append("Content-Type: \(mimeType)\r\n\r\n")
        data.append(fileData)
        append("\r\n")
    }

    private mutating func append(_ string: String) {
        data.append(Data(string.utf8))
    }

    // The full request body, including the closing boundary.
    var closedData: Data { data + Data("--\(boundary)--\r\n".utf8) }
}

enum Keychain {
    static func save(_ value: String, key: String) {
        delete(key: key)
        let query: [String: Any] = [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrAccount as String: key,
            kSecValueData as String: Data(value.utf8),
            kSecAttrAccessible as String: kSecAttrAccessibleAfterFirstUnlock
        ]
        SecItemAdd(query as CFDictionary, nil)
    }

    static func read(key: String) -> String? {
        let query: [String: Any] = [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrAccount as String: key,
            kSecReturnData as String: true,
            kSecMatchLimit as String: kSecMatchLimitOne
        ]
        var result: AnyObject?
        guard SecItemCopyMatching(query as CFDictionary, &result) == errSecSuccess,
              let data = result as? Data else { return nil }
        return String(data: data, encoding: .utf8)
    }

    static func delete(key: String) {
        let query: [String: Any] = [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrAccount as String: key
        ]
        SecItemDelete(query as CFDictionary)
    }
}
