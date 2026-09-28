import SwiftUI

struct ContentView: View {
    @EnvironmentObject var authViewModel: AuthViewModel
    @StateObject private var reportService = ReportService()
    @State private var showingNewReport = false

    var body: some View {
        TabView {
            NavigationStack {
                MapView(reportService: reportService, showingNewReport: $showingNewReport)
                    .navigationBarTitleDisplayMode(.inline)
                    .toolbar {
                        // The title sits on the same row as the account button.
                        ToolbarItem(placement: .topBarLeading) {
                            Text("RoadFix")
                                .font(.title.bold())
                        }
                        .sharedBackgroundVisibility(.hidden)

                        ToolbarItem(placement: .topBarTrailing) {
                            accountMenu
                        }
                    }
            }
            .tabItem {
                Label("Map", systemImage: "map.fill")
            }

            NavigationStack {
                MyReportsView(reportService: reportService)
                    .navigationTitle("My Reports")
            }
            .tabItem {
                Label("My Reports", systemImage: "list.bullet.rectangle")
            }

            // Only staff accounts get the dashboard. The role comes from the
            // user's Firestore profile, and firestore.rules enforces the same
            // check server-side for status changes.
            if authViewModel.currentUser?.isStaff == true {
                NavigationStack {
                    StaffDashboardView(reportService: reportService)
                        .navigationTitle("Staff Dashboard")
                }
                .tabItem {
                    Label("Staff", systemImage: "checklist")
                }
            }

            NavigationStack {
                SettingsView()
                    .navigationTitle("Settings")
            }
            .tabItem {
                Label("Settings", systemImage: "gearshape")
            }
        }
        .sheet(isPresented: $showingNewReport) {
            NewReportView(reportService: reportService)
        }
        .onAppear {
            reportService.startListening()
        }
        .onDisappear {
            reportService.stopListening()
        }
    }

    private var accountMenu: some View {
        Menu {
            if let user = authViewModel.currentUser {
                Text(user.email)
                Text("Role: \(user.role)")
            }
            Button("Sign Out", role: .destructive) {
                authViewModel.signOut()
            }
        } label: {
            Image(systemName: "person.crop.circle")
        }
    }
}

#Preview {
    ContentView()
        .environmentObject(AuthViewModel())
}
