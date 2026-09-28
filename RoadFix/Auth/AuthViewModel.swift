//
//  AuthViewModel.swift
//  RoadFix
//

import Foundation
import FirebaseAuth
import FirebaseCore
import FirebaseFirestore

@MainActor
final class AuthViewModel: ObservableObject {
    @Published var currentUser: AppUser?
    @Published var isLoading: Bool = true
    @Published var errorMessage: String?
    // True while a sign-in or sign-up request is running, so the button can
    // show a spinner and can't be tapped twice.
    @Published var isSubmitting: Bool = false

    // During sign-up the auth listener fires before the profile doc exists;
    // keep isSubmitting on until createUserProfile finishes.
    private var isCreatingAccount = false
    private var authStateHandle: AuthStateDidChangeListenerHandle?
    private let db = Firestore.firestore()

    init() {
        // Guards SwiftUI Previews (and any other early instantiation) that
        // never go through RoadFixApp.init(), which is the normal place
        // FirebaseApp.configure() runs. Calling configure() before any
        // Firebase API is required, or Auth.auth() below crashes.
        if FirebaseApp.app() == nil {
            FirebaseApp.configure()
        }
        authStateHandle = Auth.auth().addStateDidChangeListener { [weak self] _, user in
            guard let self else { return }
            if let user {
                self.fetchUserProfile(uid: user.uid)
            } else {
                self.currentUser = nil
                self.isLoading = false
            }
        }
    }

    deinit {
        if let authStateHandle {
            Auth.auth().removeStateDidChangeListener(authStateHandle)
        }
    }

    private func fetchUserProfile(uid: String) {
        db.collection("users").document(uid).getDocument { [weak self] snapshot, _ in
            guard let self else { return }
            defer { self.isLoading = false }
            guard let data = snapshot?.data(),
                  let email = data["email"] as? String,
                  let role = data["role"] as? String else {
                self.errorMessage = "Could not load your account profile."
                self.currentUser = nil
                if !self.isCreatingAccount { self.isSubmitting = false }
                return
            }
            self.currentUser = AppUser(id: uid, email: email, role: role)
            self.isSubmitting = false
        }
    }

    func signIn(email: String, password: String) {
        errorMessage = nil
        isSubmitting = true
        Auth.auth().signIn(withEmail: email, password: password) { [weak self] _, error in
            if let error {
                self?.errorMessage = self?.mapAuthError(error)
                self?.isSubmitting = false
            }
        }
    }

    func signUp(email: String, password: String, staffCode: String?) {
        errorMessage = nil
        isSubmitting = true
        isCreatingAccount = true
        Auth.auth().createUser(withEmail: email, password: password) { [weak self] result, error in
            guard let self else { return }
            if let error {
                self.errorMessage = self.mapAuthError(error)
                self.finishSubmitting()
                return
            }
            guard let firebaseUser = result?.user else {
                self.errorMessage = "Something went wrong, try again."
                self.finishSubmitting()
                return
            }
            self.resolveRole(staffCode: staffCode) { role in
                self.createUserProfile(uid: firebaseUser.uid, email: email, role: role, createdUser: firebaseUser)
            }
        }
    }

    private func resolveRole(staffCode: String?, completion: @escaping (String) -> Void) {
        guard let staffCode, !staffCode.trimmingCharacters(in: .whitespaces).isEmpty else {
            completion(AppUser.citizenRole)
            return
        }
        db.collection("config").document("staffInviteCode").getDocument { snapshot, _ in
            let storedCode = snapshot?.data()?["code"] as? String
            completion(storedCode == staffCode ? AppUser.staffRole : AppUser.citizenRole)
        }
    }

    private func createUserProfile(uid: String, email: String, role: String, createdUser: User) {
        let data: [String: Any] = [
            "email": email,
            "role": role,
            "createdAt": FieldValue.serverTimestamp()
        ]
        db.collection("users").document(uid).setData(data) { [weak self] error in
            guard let self else { return }
            if error != nil {
                // This is a Firestore error, not an Auth error — mapAuthError
                // only understands AuthErrorCode, so don't route it there.
                self.errorMessage = "Could not finish creating your account. Try again."
                createdUser.delete(completion: nil)
                try? Auth.auth().signOut()
                self.finishSubmitting()
                return
            }
            self.currentUser = AppUser(id: uid, email: email, role: role)
            self.errorMessage = nil
            self.finishSubmitting()
        }
    }

    private func finishSubmitting() {
        isSubmitting = false
        isCreatingAccount = false
    }

    func signOut() {
        do {
            try Auth.auth().signOut()
            currentUser = nil
            errorMessage = nil
            finishSubmitting()
        } catch {
            errorMessage = "Could not sign out. Try again."
        }
    }

    private func mapAuthError(_ error: Error) -> String {
        let nsError = error as NSError
        guard let code = AuthErrorCode(rawValue: nsError.code) else {
            return "Something went wrong, try again."
        }
        switch code {
        case .wrongPassword, .invalidCredential, .userNotFound:
            // Deliberately the same message for "wrong password" and "no
            // such account" — distinguishing them lets an attacker enumerate
            // which emails have accounts.
            return "Incorrect email or password."
        case .emailAlreadyInUse:
            return "An account with that email already exists."
        case .invalidEmail:
            return "That email address doesn't look right."
        case .weakPassword:
            return "Password must be at least 6 characters."
        case .networkError:
            return "Network error. Check your connection and try again."
        default:
            return "Something went wrong, try again."
        }
    }
}
