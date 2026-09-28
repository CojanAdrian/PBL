import Foundation
import CoreLocation
import UIKit
import Combine
import FirebaseAuth
import FirebaseFirestore
import FirebaseStorage

class ReportService: ObservableObject {
    @Published var reports: [Report] = []

    private let db = Firestore.firestore()
    private let storage = Storage.storage()
    private var listener: ListenerRegistration?

    private var reportsCollection: CollectionReference {
        db.collection("reports")
    }

    // Live-syncs every report so the map, dashboard and "My Reports" update
    // as soon as anyone submits, upvotes or changes a status.
    func startListening() {
        guard listener == nil else { return }
        listener = reportsCollection.addSnapshotListener { [weak self] snapshot, error in
            guard let self, let documents = snapshot?.documents else {
                if let error { print("Reports listener error: \(error.localizedDescription)") }
                return
            }
            self.reports = documents
                .compactMap { Self.report(id: $0.documentID, data: $0.data(with: .estimate)) }
                .sorted { $0.createdAt > $1.createdAt }
        }
    }

    func stopListening() {
        listener?.remove()
        listener = nil
    }

    func submitReport(
        category: ReportCategory,
        description: String,
        image: UIImage?,
        coordinate: CLLocationCoordinate2D,
        completion: @escaping (Bool) -> Void
    ) {
        guard let uid = Auth.auth().currentUser?.uid else {
            completion(false)
            return
        }
        let document = reportsCollection.document()
        uploadPhoto(image, reportID: document.documentID) { photoURL in
            var data: [String: Any] = [
                "category": category.rawValue,
                "description": description,
                "latitude": coordinate.latitude,
                "longitude": coordinate.longitude,
                "status": ReportStatus.reported.rawValue,
                "upvoterIds": [String](),
                "reporterId": uid,
                "createdAt": FieldValue.serverTimestamp()
            ]
            if let photoURL {
                data["photoURL"] = photoURL
            }
            document.setData(data) { error in
                if let error { print("Submit report error: \(error.localizedDescription)") }
                completion(error == nil)
            }
        }
    }

    // A second tap removes the upvote, so each user counts at most once.
    func toggleUpvote(_ report: Report) {
        guard let uid = Auth.auth().currentUser?.uid else { return }
        let change = report.isUpvoted(by: uid)
            ? FieldValue.arrayRemove([uid])
            : FieldValue.arrayUnion([uid])
        reportsCollection.document(report.id).updateData(["upvoterIds": change])
    }

    // firestore.rules only lets staff change a report's status.
    func updateStatus(_ report: Report, to status: ReportStatus) {
        reportsCollection.document(report.id).updateData(["status": status.rawValue])
    }

    // The report is still saved (without a photo) if the upload fails.
    private func uploadPhoto(_ image: UIImage?, reportID: String, completion: @escaping (String?) -> Void) {
        guard let image, let data = image.jpegData(compressionQuality: 0.7) else {
            completion(nil)
            return
        }
        let ref = storage.reference().child("reports/\(reportID).jpg")
        let metadata = StorageMetadata()
        metadata.contentType = "image/jpeg"
        ref.putData(data, metadata: metadata) { _, error in
            if let error {
                print("Photo upload error: \(error.localizedDescription)")
                completion(nil)
                return
            }
            ref.downloadURL { url, _ in
                completion(url?.absoluteString)
            }
        }
    }

    private static func report(id: String, data: [String: Any]) -> Report? {
        guard let categoryRaw = data["category"] as? String,
              let category = ReportCategory(rawValue: categoryRaw),
              let description = data["description"] as? String,
              let latitude = data["latitude"] as? Double,
              let longitude = data["longitude"] as? Double,
              let statusRaw = data["status"] as? String,
              let status = ReportStatus(rawValue: statusRaw),
              let reporterId = data["reporterId"] as? String else {
            return nil
        }
        return Report(
            id: id,
            category: category,
            description: description,
            photoURL: data["photoURL"] as? String,
            latitude: latitude,
            longitude: longitude,
            status: status,
            upvoterIds: data["upvoterIds"] as? [String] ?? [],
            reporterId: reporterId,
            createdAt: (data["createdAt"] as? Timestamp)?.dateValue() ?? Date()
        )
    }
}
