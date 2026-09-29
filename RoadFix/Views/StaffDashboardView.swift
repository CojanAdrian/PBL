import SwiftUI

struct StaffDashboardView: View {
    @ObservedObject var reportService: ReportService

    var body: some View {
        List(reportService.reports) { report in
            VStack(alignment: .leading, spacing: 6) {
                HStack {
                    Label(report.category.title, systemImage: report.category.systemImage)
                        .font(.subheadline.bold())
                    Spacer()
                    StatusBadge(status: report.status)
                }

                Text(report.description)
                    .font(.caption)
                    .foregroundStyle(.secondary)
                    .lineLimit(2)

                Picker("Status", selection: Binding(
                    get: { report.status },
                    set: { newStatus in reportService.updateStatus(report, to: newStatus) }
                )) {
                    ForEach(ReportStatus.allCases, id: \.self) { status in
                        Text(status.title).tag(status)
                    }
                }
                .pickerStyle(.segmented)
            }
            .padding(.vertical, 4)
        }
    }
}
