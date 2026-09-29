//
//  AuthViewModel.swift
//  RoadFix
//

import Foundation
import Combine

@MainActor
final class AuthViewModel: ObservableObject {
    @Published var currentUser: AppUser?
    @Published var isLoading: Bool = true
    @Published var errorMessage: String?
    // True while a sign-in or sign-up request is running, so the button can
    // show a spinner and can't be tapped twice.
    @Published var isSubmitting: Bool = false

    private var sessionObserver: NSObjectProtocol?

    init() {
        // If the server rejects our token at any point (expired, account
        // deleted), drop back to the login screen.
        sessionObserver = NotificationCenter.default.addObserver(
            forName: .apiSessionExpired, object: nil, queue: .main
        ) { [weak self] _ in
            Task { @MainActor in self?.endSession() }
        }
        restoreSession()
    }

    deinit {
        if let sessionObserver {
            NotificationCenter.default.removeObserver(sessionObserver)
        }
    }

    // On launch, use the token saved in the Keychain (if any) to skip the
    // login screen.
    private func restoreSession() {
        guard APIClient.shared.token != nil else {
            isLoading = false
            return
        }
        Task {
            defer { isLoading = false }
            do {
                currentUser = Self.appUser(try await APIClient.shared.me())
            } catch let error as APIError where error.isUnauthorized {
                endSession()
            } catch {
                // Offline at launch: keep the token, show login for now.
                errorMessage = (error as? APIError)?.message
            }
        }
    }

    func signIn(email: String, password: String) {
        errorMessage = nil
        isSubmitting = true
        Task {
            defer { isSubmitting = false }
            do {
                currentUser = Self.appUser(try await APIClient.shared.signIn(email: email, password: password))
            } catch {
                errorMessage = (error as? APIError)?.message ?? APIError.unknown.message
            }
        }
    }

    func signUp(email: String, password: String, staffCode: String?) {
        errorMessage = nil
        isSubmitting = true
        Task {
            defer { isSubmitting = false }
            do {
                currentUser = Self.appUser(
                    try await APIClient.shared.signUp(email: email, password: password, staffCode: staffCode)
                )
            } catch {
                errorMessage = (error as? APIError)?.message ?? APIError.unknown.message
            }
        }
    }

    func signOut() {
        endSession()
    }

    private func endSession() {
        APIClient.shared.setToken(nil)
        currentUser = nil
        errorMessage = nil
        isSubmitting = false
    }

    private static func appUser(_ user: APIUser) -> AppUser {
        AppUser(id: user.id, email: user.email, role: user.role)
    }
}
