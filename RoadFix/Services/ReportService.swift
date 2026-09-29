import Foundation
import CoreLocation
import UIKit
import Combine

class ReportService: ObservableObject {
    @Published var reports: [Report] = []

    private var pollTask: Task<Void, Never>?

    // Re-fetches every report every few seconds so the map, dashboard and
    // "My Reports" pick up what other people submit, upvote or resolve.
    func startListening() {
        guard pollTask == nil else { return }
        pollTask = Task { [weak self] in
            while !Task.isCancelled {
                guard let self else { return }
                await self.refresh()
                try? await Task.sleep(for: .seconds(5))
            }
        }
    }

    func stopListening() {
        pollTask?.cancel()
        pollTask = nil
    }

    func refresh() async {
        do {
            reports = try await APIClient.shared.fetchReports()
        } catch {
            print("Fetch reports error: \(error.localizedDescription)")
        }
    }

    func submitReport(
        category: ReportCategory,
        description: String,
        image: UIImage?,
        coordinate: CLLocationCoordinate2D,
        completion: @escaping (Bool) -> Void
    ) {
        Task {
            do {
                let report = try await APIClient.shared.createReport(
                    category: category,
                    description: description,
                    latitude: coordinate.latitude,
                    longitude: coordinate.longitude,
                    photoJPEG: image?.jpegData(compressionQuality: 0.7)
                )
                merge(report)
                completion(true)
            } catch {
                print("Submit report error: \(error.localizedDescription)")
                completion(false)
            }
        }
    }

    // A second tap removes the upvote, so each user counts at most once.
    func toggleUpvote(_ report: Report) {
        Task {
            do {
                merge(try await APIClient.shared.toggleUpvote(reportID: report.id))
            } catch {
                print("Upvote error: \(error.localizedDescription)")
            }
        }
    }

    // The server only lets staff change a report's status.
    func updateStatus(_ report: Report, to status: ReportStatus) {
        Task {
            do {
                merge(try await APIClient.shared.updateStatus(reportID: report.id, status: status))
            } catch {
                print("Update status error: \(error.localizedDescription)")
            }
        }
    }

    // Applies a server response right away instead of waiting for the next poll.
    private func merge(_ report: Report) {
        if let index = reports.firstIndex(where: { $0.id == report.id }) {
            reports[index] = report
        } else {
            reports.insert(report, at: 0)
        }
        reports.sort { $0.createdAt > $1.createdAt }
    }
}
