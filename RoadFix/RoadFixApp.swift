//
//  RoadFixApp.swift
//  RoadFix
//
//  Created by Vlad Buliga on 22/09/2026.
//

import SwiftUI
import FirebaseCore

@main
struct RoadFixApp: App {
    init() {
        FirebaseApp.configure()
    }

    var body: some Scene {
        WindowGroup {
            RootView()
        }
    }
}
