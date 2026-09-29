import SwiftUI

struct ReportDetailView: View {
    let reportID: String
    @ObservedObject var reportService: ReportService
    @EnvironmentObject var authViewModel: AuthViewModel
    @Environment(\.dismiss) private var dismiss

    // Looked up live (not captured as a copy) so upvotes and status changes
    // show up while the sheet is open.
    private var report: Report? {
        reportService.reports.first { $0.id == reportID }
    }

    var body: some View {
        NavigationStack {
            Group {
                if let report {
                    details(for: report)
                } else {
                    ContentUnavailableView("Report Unavailable", systemImage: "exclamationmark.triangle")
                }
            }
            .navigationTitle("Report Detail")
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Close") { dismiss() }
                }
            }
        }
    }

    private func details(for report: Report) -> some View {
        let upvoted = report.isUpvoted(by: authViewModel.currentUser?.id)
        return ScrollView {
            VStack(alignment: .leading, spacing: 16) {
                if let urlString = report.photoURL, let url = URL(string: urlString) {
                    AsyncImage(url: url) { image in
                        image.resizable().scaledToFill()
                    } placeholder: {
                        ProgressView()
                    }
                    .frame(height: 220)
                    .frame(maxWidth: .infinity)
                    .clipped()
                    .cornerRadius(12)
                }

                HStack {
                    Label(report.category.title, systemImage: report.category.systemImage)
                        .font(.headline)
                    Spacer()
                    StatusBadge(status: report.status)
                }

                Text(report.description)
                    .font(.body)

                Text(report.createdAt.formatted(date: .abbreviated, time: .shortened))
                    .font(.caption)
                    .foregroundStyle(.secondary)

                Button {
                    reportService.toggleUpvote(report)
                } label: {
                    Label(
                        upvoted ? "Upvoted (\(report.upvoteCount))" : "Upvote (\(report.upvoteCount))",
                        systemImage: upvoted ? "hand.thumbsup.fill" : "hand.thumbsup"
                    )
                }
                .buttonStyle(.borderedProminent)
            }
            .padding()
        }
    }
}

struct StatusBadge: View {
    let status: ReportStatus

    var body: some View {
        Text(status.title)
            .font(.caption.bold())
            .padding(.horizontal, 10)
            .padding(.vertical, 4)
            .background(status.color.opacity(0.2))
            .foregroundStyle(status.color)
            .clipShape(Capsule())
    }
}

extension ReportStatus {
    var color: Color {
        switch self {
        case .reported: return .red
        case .inProgress: return .orange
        case .resolved: return .green
        }
    }
}
