//
//  SignUpView.swift
//  RoadFix
//

import SwiftUI

struct SignUpView: View {
    @EnvironmentObject var authViewModel: AuthViewModel
    @State private var email = ""
    @State private var password = ""
    @State private var confirmPassword = ""
    @State private var isStaff = false
    @State private var staffCode = ""

    private var canSubmit: Bool {
        email.contains("@") && email.contains(".") &&
        password.count >= 6 &&
        password == confirmPassword
    }

    var body: some View {
        VStack(spacing: 16) {
            Text("Create Account")
                .font(.largeTitle.bold())

            TextField("Email", text: $email)
                .textInputAutocapitalization(.never)
                .keyboardType(.emailAddress)
                .textFieldStyle(.roundedBorder)

            SecureField("Password (min 6 characters)", text: $password)
                .textFieldStyle(.roundedBorder)

            SecureField("Confirm Password", text: $confirmPassword)
                .textFieldStyle(.roundedBorder)

            DisclosureGroup("I'm staff", isExpanded: $isStaff) {
                TextField("Staff invite code", text: $staffCode)
                    .textFieldStyle(.roundedBorder)
            }

            if let errorMessage = authViewModel.errorMessage {
                Text(errorMessage)
                    .foregroundStyle(.red)
                    .font(.footnote)
            }

            Button("Create Account") {
                let code = isStaff ? staffCode : nil
                authViewModel.signUp(email: email, password: password, staffCode: code)
            }
            .buttonStyle(.borderedProminent)
            .disabled(!canSubmit)
        }
        .padding()
    }
}

#Preview {
    SignUpView()
        .environmentObject(AuthViewModel())
}
