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
    @FocusState private var focus: AuthFocus?

    private var canSubmit: Bool {
        email.contains("@") && email.contains(".") &&
        password.count >= 6 &&
        password == confirmPassword
    }

    var body: some View {
        AuthScreen {
            Spacer(minLength: 0)

            AuthHeader(title: "Create Account", subtitle: "Join RoadFix to report issues around your city.")

            AuthCard {
                AuthField(
                    systemImage: "envelope",
                    placeholder: "Email",
                    text: $email,
                    contentType: .username,
                    keyboard: .emailAddress,
                    focus: $focus,
                    field: .email
                )
                .submitLabel(.next)
                .onSubmit { focus = .password }

                AuthCardDivider()

                AuthField(
                    systemImage: "lock",
                    placeholder: "Password (min 6 characters)",
                    text: $password,
                    isSecure: true,
                    contentType: .newPassword,
                    focus: $focus,
                    field: .password
                )
                .submitLabel(.next)
                .onSubmit { focus = .confirmPassword }

                AuthCardDivider()

                AuthField(
                    systemImage: "lock.rotation",
                    placeholder: "Confirm Password",
                    text: $confirmPassword,
                    isSecure: true,
                    contentType: .newPassword,
                    focus: $focus,
                    field: .confirmPassword
                )
                .submitLabel(isStaff ? .next : .go)
                .onSubmit {
                    if isStaff { focus = .staffCode } else { submit() }
                }
            }

            DisclosureGroup("I'm staff", isExpanded: $isStaff) {
                AuthCard {
                    AuthField(
                        systemImage: "key",
                        placeholder: "Staff invite code",
                        text: $staffCode,
                        focus: $focus,
                        field: .staffCode
                    )
                    .submitLabel(.go)
                    .onSubmit(submit)
                }
                .padding(.top, 8)
            }
            .tint(.primary)
            .padding(.horizontal, 4)

            AuthErrorText(message: authViewModel.errorMessage)

            AuthPrimaryButton(
                title: "Create Account",
                isLoading: authViewModel.isSubmitting,
                isEnabled: canSubmit,
                action: submit
            )

            Spacer(minLength: 0)
        }
        .navigationBarTitleDisplayMode(.inline)
    }

    private func submit() {
        guard canSubmit, !authViewModel.isSubmitting else { return }
        focus = nil
        let code = isStaff ? staffCode : nil
        authViewModel.signUp(email: email, password: password, staffCode: code)
    }
}

#Preview {
    NavigationStack {
        SignUpView()
            .environmentObject(AuthViewModel())
    }
}
