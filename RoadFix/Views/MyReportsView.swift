import SwiftUI

struct MyReportsView: View {
    @ObservedObject var reportService: ReportService
    @EnvironmentObject var authViewModel: AuthViewModel
    @State private var selectedReport: Report?

    private var myReports: [Report] {
        guard let uid = authViewModel.currentUser?.id else { return [] }
        return reportService.reports.filter { $0.reporterId == uid }
    }

    var body: some View {
        Group {
            if myReports.isEmpty {
                ContentUnavailableView(
                    "No Reports Yet",
                    systemImage: "tray",
                    description: Text("Issues you report from the map will show up here.")
                )
            } else {
                List(myReports) { report in
                    Button {
                        selectedReport = report
                    } label: {
                        MyReportRow(report: report)
                    }
                    .tint(.primary)
                }
            }
        }
        .sheet(item: $selectedReport) { report in
            ReportDetailView(reportID: report.id, reportService: reportService)
        }
    }
}

private struct MyReportRow: View {
    let report: Report

    var body: some View {
        HStack(spacing: 12) {
            Image(systemName: report.category.systemImage)
                .font(.system(size: 14, weight: .bold))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(report.status.color)
                .clipShape(Circle())

            VStack(alignment: .leading, spacing: 2) {
                Text(report.category.title)
                    .font(.subheadline.bold())
                Text(report.description)
                    .font(.caption)
                    .foregroundStyle(.secondary)
                    .lineLimit(1)
                Text(report.createdAt.formatted(date: .abbreviated, time: .shortened))
                    .font(.caption2)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            VStack(alignment: .trailing, spacing: 6) {
                StatusBadge(status: report.status)
                Label("\(report.upvoteCount)", systemImage: "hand.thumbsup.fill")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
        }
    }
}
