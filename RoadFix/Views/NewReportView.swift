import SwiftUI
import PhotosUI
import CoreLocation

struct NewReportView: View {
    @ObservedObject var reportService: ReportService
    @StateObject private var locationManager = LocationManager()
    @Environment(\.dismiss) private var dismiss
    @Environment(\.openURL) private var openURL

    @State private var category: ReportCategory = .pothole
    @State private var description: String = ""
    @State private var selectedItem: PhotosPickerItem?
    @State private var selectedImage: UIImage?
    @State private var isSubmitting = false
    @State private var submitError: String?

    private var locationDenied: Bool {
        locationManager.authorizationStatus == .denied || locationManager.authorizationStatus == .restricted
    }

    var body: some View {
        NavigationStack {
            Form {
                Section("Photo") {
                    PhotosPicker(selection: $selectedItem, matching: .images) {
                        if let selectedImage {
                            Image(uiImage: selectedImage)
                                .resizable()
                                .scaledToFit()
                                .frame(maxHeight: 200)
                        } else {
                            Label("Add a photo", systemImage: "camera.fill")
                        }
                    }
                    .onChange(of: selectedItem) { _, newItem in
                        Task {
                            if let data = try? await newItem?.loadTransferable(type: Data.self) {
                                selectedImage = UIImage(data: data)
                            }
                        }
                    }
                }

                Section("Category") {
                    Picker("Category", selection: $category) {
                        ForEach(ReportCategory.allCases) { cat in
                            Label(cat.title, systemImage: cat.systemImage).tag(cat)
                        }
                    }
                }

                Section("Description") {
                    TextField("What's the issue?", text: $description, axis: .vertical)
                        .lineLimit(3...6)
                }

                Section("Location") {
                    if let loc = locationManager.currentLocation {
                        Text("Lat: \(loc.latitude, specifier: "%.5f"), Lng: \(loc.longitude, specifier: "%.5f")")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    } else if locationDenied {
                        Text("Location access is turned off. RoadFix needs your location to pin the issue on the map.")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                        Button("Open Settings") {
                            if let url = URL(string: UIApplication.openSettingsURLString) {
                                openURL(url)
                            }
                        }
                    } else {
                        Text("Fetching current location…")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                }
            }
            .navigationTitle("New Report")
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button(isSubmitting ? "Submitting…" : "Submit") {
                        submit()
                    }
                    .disabled(description.isEmpty || locationManager.currentLocation == nil || isSubmitting)
                }
            }
            .onAppear {
                locationManager.requestLocation()
            }
            .alert("Couldn't Submit Report", isPresented: Binding(
                get: { submitError != nil },
                set: { if !$0 { submitError = nil } }
            )) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(submitError ?? "")
            }
        }
    }

    private func submit() {
        guard let coordinate = locationManager.currentLocation else { return }
        isSubmitting = true
        reportService.submitReport(
            category: category,
            description: description,
            image: selectedImage,
            coordinate: coordinate
        ) { success in
            isSubmitting = false
            if success {
                dismiss()
            } else {
                submitError = "Check your connection and try again."
            }
        }
    }
}
