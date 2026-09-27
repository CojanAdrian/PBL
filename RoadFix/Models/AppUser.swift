//
//  AppUser.swift
//  RoadFix
//

import Foundation

struct AppUser: Identifiable, Equatable {
    let id: String        // Firebase Auth uid
    let email: String
    let role: String      // "citizen" or "staff"

    static let citizenRole = "citizen"
    static let staffRole = "staff"

    var isStaff: Bool { role == AppUser.staffRole }
}
