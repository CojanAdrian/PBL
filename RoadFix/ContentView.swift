//
//  ContentView.swift
//  RoadFix
//
//  Created by Vlad Buliga on 22/09/2026.
//

import SwiftUI

// TODO: Replace this placeholder body with the real tab view (citizen Map
// tab + Staff Dashboard tab) once that work is merged into this branch.
struct ContentView: View {
    @EnvironmentObject var authViewModel: AuthViewModel

    var body: some View {
        VStack(spacing: 16) {
            if let user = authViewModel.currentUser {
                Text("Signed in as \(user.email)")
                Text("Role: \(user.role)")
                    .foregroundStyle(.secondary)
            }
            Button("Sign Out") {
                authViewModel.signOut()
            }
            .buttonStyle(.bordered)
        }
        .padding()
    }
}

#Preview {
    ContentView()
        .environmentObject(AuthViewModel())
}
