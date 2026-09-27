# Auth & Firebase Groundwork Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **No git commits.** Do not run `git add`/`git commit` at the end of any task in this plan, and do not add a `Co-Authored-By` trailer. Leave changes staged/unstaged for the user (Adrian) to review and commit himself. (This overrides the "Commit" step that normally closes out a task.)
>
> **No local build/test runner.** This plan is executed on a machine with no Xcode/Swift toolchain. Every step that would normally be "run the test/build and confirm output" is instead a manual step for the human to perform in Xcode on a Mac — write the step down precisely, but do not attempt to execute `xcodebuild`/`swift build` here.

**Goal:** Add Firebase Auth (email/password) + Firestore-backed login/signup to the RoadFix SwiftUI app, with a citizen/staff role captured at signup and a baseline Firestore security-rules file enforcing that role server-side.

**Architecture:** A single `AuthViewModel` (ObservableObject) wraps Firebase Auth + a `users/{uid}` Firestore doc. `RootView` becomes the app's new entry point, switching between `LoginView`/`SignUpView` and the existing `ContentView` (temporarily replaced with a placeholder) based on `AuthViewModel.currentUser`. A `firestore.rules` file at the repo root gives Adrian's RBAC gap a real server-side backstop.

**Tech Stack:** SwiftUI, Firebase Auth (email/password), Firebase Firestore, Firebase iOS SDK via Swift Package Manager.

**Spec:** `docs/superpowers/specs/2026-09-27-auth-firebase-design.md`

---

### Task 0: Manual prerequisites (Firebase console + Xcode SPM package)

**Files:** none — this task only exists outside the repo (Firebase console) and in Xcode's package management UI, which can't be done by an agent editing text files.

This task must be done by Adrian, on the Mac that has Xcode, before Task 2 onward will compile. Nothing here is scriptable from this environment.

- [ ] **Step 1: Create the Firebase project**
  Go to console.firebase.google.com → Add project → name it "RoadFix" → skip/disable Google Analytics.

- [ ] **Step 2: Enable Email/Password auth**
  In the new project: Build → Authentication → Get started → Sign-in method tab → enable **Email/Password** → Save.

- [ ] **Step 3: Create Firestore in test mode**
  Build → Firestore Database → Create database → **Start in test mode** → pick a region close to you → Enable.

- [ ] **Step 4: Create the staff invite code doc**
  In the Firestore console: Start collection → collection ID `config` → document ID `staffInviteCode` → add field `code` (type: string), value of your choosing, e.g. `ROADFIX-STAFF-2026` → Save.
  Expected: a `config/staffInviteCode` document exists with one string field `code`.

- [ ] **Step 5: Register the iOS app**
  Project settings (gear icon, top left) → Your apps → Add app → iOS. In Xcode, check the exact Bundle Identifier on the RoadFix target (Signing & Capabilities tab) and enter that same value as the iOS bundle ID in the Firebase console. Nickname it "RoadFix". Skip App Store ID.

- [ ] **Step 6: Add GoogleService-Info.plist**
  Download the generated `GoogleService-Info.plist` from that same registration flow. In Xcode, drag it into the `RoadFix/` group (next to `RoadFixApp.swift`) → check "Copy items if needed" and "Add to targets: RoadFix" → Finish.
  Expected: `GoogleService-Info.plist` appears in the Xcode file navigator under `RoadFix/`, and in Build Phases → Copy Bundle Resources.

- [ ] **Step 7: Add the Firebase SDK package**
  In Xcode: File → Add Package Dependencies → enter `https://github.com/firebase/firebase-ios-sdk` → Add Package → when prompted for products, check **FirebaseAuth** and **FirebaseFirestore** → add both to the RoadFix target → Add Package.
  Expected: `firebase-ios-sdk` appears under Package Dependencies in the project navigator, with FirebaseAuth and FirebaseFirestore listed as linked libraries on the RoadFix target.

---

### Task 1: AppUser model

**Files:**
- Create: `RoadFix/Models/AppUser.swift`

- [ ] **Step 1: Write the file**

```swift
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
```

- [ ] **Step 2: Manual check (Xcode, on your Mac)**
  Add the file to the RoadFix target if Xcode doesn't do so automatically (new files created outside Xcode need "Add Files to RoadFix..." if they don't show up). Build (⌘B).
  Expected: builds with no errors. `AppUser` has no dependency on Firebase, so this should succeed even before Task 0's SPM package is added.

---

### Task 2: AuthViewModel

**Files:**
- Create: `RoadFix/Auth/AuthViewModel.swift`
- Depends on: Task 0 (Firebase SDK added), Task 1 (`AppUser`)

- [ ] **Step 1: Write the file**

```swift
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
                return
            }
            self.currentUser = AppUser(id: uid, email: email, role: role)
        }
    }

    func signIn(email: String, password: String) {
        errorMessage = nil
        Auth.auth().signIn(withEmail: email, password: password) { [weak self] _, error in
            if let error {
                self?.errorMessage = self?.mapAuthError(error)
            }
        }
    }

    func signUp(email: String, password: String, staffCode: String?) {
        errorMessage = nil
        Auth.auth().createUser(withEmail: email, password: password) { [weak self] result, error in
            guard let self else { return }
            if let error {
                self.errorMessage = self.mapAuthError(error)
                return
            }
            guard let firebaseUser = result?.user else {
                self.errorMessage = "Something went wrong, try again."
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
                return
            }
            self.currentUser = AppUser(id: uid, email: email, role: role)
            self.errorMessage = nil
        }
    }

    func signOut() {
        do {
            try Auth.auth().signOut()
            currentUser = nil
            errorMessage = nil
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
```

- [ ] **Step 2: Manual check (Xcode, on your Mac)**
  Requires Task 0 done first (Firebase SDK + GoogleService-Info.plist present), otherwise this will not compile.
  Build (⌘B).
  Expected: builds with no errors. If Xcode flags `AuthErrorCode` cases as unavailable/renamed, your installed Firebase SDK version uses slightly different case names — use Xcode's autocomplete on `AuthErrorCode.` to find the closest matching cases (e.g. some SDK versions use `.invalidCredential` instead of `.wrongPassword` for wrong-password errors) and adjust the `switch` accordingly; the `default` case means nothing breaks even before you adjust it.

---

### Task 3: LoginView

**Files:**
- Create: `RoadFix/Auth/LoginView.swift`
- Depends on: Task 2 (`AuthViewModel`)

- [ ] **Step 1: Write the file**

```swift
//
//  LoginView.swift
//  RoadFix
//

import SwiftUI

struct LoginView: View {
    @EnvironmentObject var authViewModel: AuthViewModel
    @State private var email = ""
    @State private var password = ""
    @State private var showSignUp = false

    private var canSubmit: Bool {
        !email.isEmpty && !password.isEmpty
    }

    var body: some View {
        NavigationStack {
            VStack(spacing: 16) {
                Text("RoadFix")
                    .font(.largeTitle.bold())

                TextField("Email", text: $email)
                    .textInputAutocapitalization(.never)
                    .keyboardType(.emailAddress)
                    .textFieldStyle(.roundedBorder)

                SecureField("Password", text: $password)
                    .textFieldStyle(.roundedBorder)

                if let errorMessage = authViewModel.errorMessage {
                    Text(errorMessage)
                        .foregroundStyle(.red)
                        .font(.footnote)
                }

                Button("Log In") {
                    authViewModel.signIn(email: email, password: password)
                }
                .buttonStyle(.borderedProminent)
                .disabled(!canSubmit)

                Button("Don't have an account? Sign Up") {
                    showSignUp = true
                }
                .font(.footnote)
            }
            .padding()
            .navigationDestination(isPresented: $showSignUp) {
                SignUpView()
            }
        }
    }
}

#Preview {
    LoginView()
        .environmentObject(AuthViewModel())
}
```

- [ ] **Step 2: Manual check (Xcode, on your Mac)**
  Open the Preview canvas for `LoginView.swift` (⌥⌘Return), or build (⌘B).
  Expected: preview renders the form; "Log In" button appears disabled (greyed out) until both fields are typed into.

---

### Task 4: SignUpView

**Files:**
- Create: `RoadFix/Auth/SignUpView.swift`
- Depends on: Task 2 (`AuthViewModel`)

- [ ] **Step 1: Write the file**

```swift
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
```

- [ ] **Step 2: Manual check (Xcode, on your Mac)**
  Open the Preview canvas for `SignUpView.swift`.
  Expected: preview renders the form; "I'm staff" disclosure is collapsed by default and reveals the staff-code field when tapped; "Create Account" stays disabled until email/password/confirm rules are satisfied.

---

### Task 5: RootView + wire into RoadFixApp

**Files:**
- Create: `RoadFix/RootView.swift`
- Modify: `RoadFix/RoadFixApp.swift`
- Depends on: Task 2, Task 3, Task 4, and Task 6 (`ContentView` placeholder — write this task and Task 6 together since `RootView` references `ContentView`)

- [ ] **Step 1: Write `RootView.swift`**

```swift
//
//  RootView.swift
//  RoadFix
//

import SwiftUI

struct RootView: View {
    @StateObject private var authViewModel = AuthViewModel()

    var body: some View {
        Group {
            if authViewModel.isLoading {
                ProgressView("Loading...")
            } else if authViewModel.currentUser != nil {
                ContentView()
                    .environmentObject(authViewModel)
            } else {
                LoginView()
                    .environmentObject(authViewModel)
            }
        }
    }
}

#Preview {
    RootView()
}
```

- [ ] **Step 2: Edit `RoadFixApp.swift`**

Current content (`RoadFix/RoadFixApp.swift`):

```swift
//
//  RoadFixApp.swift
//  RoadFix
//
//  Created by Vlad Buliga on 22/09/2026.
//

import SwiftUI

@main
struct RoadFixApp: App {
    var body: some Scene {
        WindowGroup {
            ContentView()
        }
    }
}
```

Replace with:

```swift
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
```

- [ ] **Step 3: Manual check (Xcode, on your Mac)**
  Build and run (⌘R) on the Simulator. Requires Task 0 (GoogleService-Info.plist + SPM package) and Task 6 (`ContentView` placeholder) to already be in place.
  Expected: app launches, briefly shows a loading spinner, then lands on the Login screen (since no one is signed in yet). No crash on launch (a crash here almost always means `GoogleService-Info.plist` is missing or wasn't added to the target — recheck Task 0 Step 6).

---

### Task 6: ContentView placeholder

**Files:**
- Modify: `RoadFix/ContentView.swift`
- Depends on: Task 2 (`AuthViewModel`)

- [ ] **Step 1: Edit the file**

Current content (`RoadFix/ContentView.swift`):

```swift
//
//  ContentView.swift
//  RoadFix
//
//  Created by Vlad Buliga on 22/09/2026.
//

import SwiftUI

struct ContentView: View {
    var body: some View {
        VStack {
            Image(systemName: "globe")
                .imageScale(.large)
                .foregroundStyle(.tint)
            Text("Hello, world!")
        }
        .padding()
    }
}

#Preview {
    ContentView()
}
```

Replace with:

```swift
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
```

- [ ] **Step 2: Manual check (Xcode, on your Mac)**
  Build (⌘B).
  Expected: builds with no errors. Full end-to-end behavior (actually reaching this screen after signing in) is verified in Task 8, once Task 5 wires `RootView` in.

---

### Task 7: Firestore security rules

**Files:**
- Create: `firestore.rules` (repo root, next to `RoadFix.xcodeproj`)

- [ ] **Step 1: Write the file**

```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    function isSignedIn() {
      return request.auth != null;
    }

    function userRole() {
      return get(/databases/$(database)/documents/users/$(request.auth.uid)).data.role;
    }

    // Each user can read/create their own profile doc, and can update it
    // as long as they don't change their own role after creation.
    match /users/{userId} {
      allow read: if isSignedIn() && request.auth.uid == userId;
      allow create: if isSignedIn() && request.auth.uid == userId;
      allow update: if isSignedIn() && request.auth.uid == userId
                    && request.resource.data.role == resource.data.role;
      allow delete: if false;
    }

    // Readable by any signed-in client (needed for the signup staff-code
    // check); never writable from a client.
    match /config/{docId} {
      allow read: if isSignedIn();
      allow write: if false;
    }

    // Baseline for the reports collection teammates' ReportService.swift
    // will write to. Assumes `status` and `reporterId` fields per the team's
    // status email, and that a new report's status literal is "reported";
    // revisit field/value names once Report.swift actually lands.
    match /reports/{reportId} {
      allow read: if isSignedIn();

      // A citizen may only create a report as themselves, and only with the
      // initial status — they cannot set an arbitrary status (e.g.
      // "resolved") at creation time. Without this, the create path defeats
      // the whole point of the update rule below.
      allow create: if isSignedIn()
                    && request.resource.data.reporterId == request.auth.uid
                    && request.resource.data.status == "reported";

      // Staff can change anything, including status. A non-staff user may
      // only update their own report, and only fields other than status.
      allow update: if isSignedIn() &&
                     (
                       userRole() == "staff" ||
                       (
                         resource.data.reporterId == request.auth.uid &&
                         request.resource.data.status == resource.data.status
                       )
                     );

      allow delete: if false;
    }
  }
}
```

- [ ] **Step 2: Manual publish (Firebase console, when ready)**
  This is not published automatically. When you're ready to turn off test mode: Firestore Database → Rules tab → paste this file's contents → Publish.
  Expected: the Rules tab shows the new rules; Firestore stops being in unrestricted test mode.
  Do this only once teammates confirm `Report.swift`/`ReportService.swift` field names match (`status`, `reporterId`), otherwise their writes may start failing — see the spec's "Deferred" section.

---

### Task 8: End-to-end manual verification

**Files:** none (manual test pass across everything above)

Requires all of Task 0–7 done. Run these on the Simulator or a device, in order, noting pass/fail as you go:

- [ ] **Step 1: Empty-field gating**
  Open the app fresh (Login screen). Leave both fields empty → confirm "Log In" is disabled. Tap "Sign Up" → leave all fields empty → confirm "Create Account" is disabled.

- [ ] **Step 2: New citizen signup**
  On Sign Up, enter a new email + a 6+ character password + matching confirm, leave "I'm staff" collapsed → tap "Create Account".
  Expected: lands on the placeholder ContentView showing `Signed in as <email>` / `Role: citizen`. In the Firebase console, Firestore → `users` collection now has a doc for that uid with `role: "citizen"`.

- [ ] **Step 3: New staff signup**
  Sign out. Sign up with a different new email, expand "I'm staff", enter the exact code you put in `config/staffInviteCode` (Task 0 Step 4) → "Create Account".
  Expected: `Role: staff` shown; Firestore `users/{uid}` doc has `role: "staff"`.

- [ ] **Step 4: Wrong staff code**
  Sign out. Sign up with a third new email, expand "I'm staff", enter an incorrect code → "Create Account".
  Expected: signup still succeeds, but `Role: citizen` is shown (no error revealing the code was wrong).

- [ ] **Step 5: Duplicate email**
  Sign out. Sign up again with the exact email used in Step 2.
  Expected: inline error "An account with that email already exists." — stays on the Sign Up screen.

- [ ] **Step 6: Wrong password on login**
  Go to Login, enter the Step 2 email with a deliberately wrong password → "Log In".
  Expected: inline error "Incorrect email or password." — no raw error text, no account-existence hint.

- [ ] **Step 7: Correct login + sign out + relaunch**
  Log in with the Step 2 email/correct password → confirm you land on ContentView. Tap "Sign Out" → confirm you land back on Login. Quit and relaunch the app.
  Expected: relaunch shows the loading spinner briefly, then Login (not auto-signed-in, since you explicitly signed out).

- [ ] **Step 8: Session persistence**
  Log in again with the Step 2 account. Without signing out, quit the app (swipe up in Simulator) and relaunch.
  Expected: relaunch skips Login entirely and goes straight to ContentView showing the signed-in user — Firebase Auth persists the session by default.

---

## Plan self-review notes

- **Spec coverage:** every spec section has a task — architecture/files (Tasks 1–6), data model + rules (Task 7), screens/flow (Tasks 3–5), error mapping (Task 2), testing plan (Task 8), manual Firebase setup (Task 0). Deferred items (Cloud Function hardening, password reset, email verification) are intentionally not tasked here — they're out of scope per the spec.
- **No placeholders:** every code step above is complete, runnable Swift/rules content, not a description of what to write.
- **Type consistency check:** `AppUser(id:email:role:)` is used identically in Task 2 and Task 6; `AuthViewModel`'s published properties (`currentUser`, `isLoading`, `errorMessage`) and methods (`signIn`, `signUp`, `signOut`) are called with matching signatures in Tasks 3, 4, 5, and 6.
