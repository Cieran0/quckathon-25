package handlers

import (
	"backend/internal/db"
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"time"
)


func makeProject(projName string, projDescription string) (int, error) {
    // Start a transaction to ensure atomicity
    tx, err := db.DB.Begin()
    if err != nil {
        return 0, fmt.Errorf("failed to begin transaction: %w", err)
    }

    // Rollback the transaction in case of any error
    defer func() {
        if err != nil {
            tx.Rollback()
        }
    }()

    // Insert the project into the database
    var projectID int
    err = tx.QueryRow(
        `INSERT INTO projects (name, description) VALUES ($1, $2) RETURNING id`,
        projName, projDescription,
    ).Scan(&projectID)
    if err != nil {
        return 0, fmt.Errorf("failed to insert project: %w", err)
    }
    log.Println("Inserted project:", projName, "ID:", projectID)

    // Insert the root folder for the project
    var rootFolderID int
    err = tx.QueryRow(
        `INSERT INTO folders (project_id, name) VALUES ($1, $2) RETURNING id`,
        projectID, "Root Folder",
    ).Scan(&rootFolderID)
    if err != nil {
        return 0, fmt.Errorf("failed to insert root folder: %w", err)
    }
    log.Println("Inserted root folder for project", projName, "ID:", rootFolderID)

    // Commit the transaction
    if err := tx.Commit(); err != nil {
        return 0, fmt.Errorf("failed to commit transaction: %w", err)
    }

    // Return the project ID and no error
    return projectID, nil
}

type ProjectRequest struct {
	SessionToken string `json:"session_token"`
	ProjectName     string `json:"name"`
	ProjectDescription     string `json:"desc"`
}

type ProjectResponse struct {
	ProjectID int `json:"id"`
}

func NewProject(w http.ResponseWriter, r *http.Request) {
    // Handle preflight OPTIONS requests for CORS
    if r.Method == http.MethodOptions {
        setCORSHeaders(w)
        w.WriteHeader(http.StatusOK)
        return
    }

    // Ensure only POST requests are allowed
    if r.Method != http.MethodPost {
        http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
        return
    }

    // Set CORS headers for POST requests
    setCORSHeaders(w)

    // Decode the incoming JSON request
    var req ProjectRequest
    if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
        http.Error(w, "Invalid request body", http.StatusBadRequest)
        return
    }
    log.Printf("Received project request: %+v", req)

    // Validate the session token
    var tokenUserID int
    var expiresAt time.Time
    err := db.DB.QueryRow(
        "SELECT user_id, expires_at FROM session_tokens WHERE token = $1",
        req.SessionToken,
    ).Scan(&tokenUserID, &expiresAt)
    if err != nil {
        if err == sql.ErrNoRows {
            http.Error(w, "Invalid session token", http.StatusUnauthorized)
            return
        }
        http.Error(w, "Internal server error", http.StatusInternalServerError)
        return
    }

    // Check if the session token has expired
    if time.Now().After(expiresAt) {
        http.Error(w, "Session token expired", http.StatusUnauthorized)
        return
    }

    // Create the new project
    projectID, err := makeProject(req.ProjectName, req.ProjectDescription)
    if err != nil {
        http.Error(w, fmt.Sprintf("Failed to create project: %v", err), http.StatusInternalServerError)
        return
    }

    // Prepare the response
    response := ProjectResponse{
        ProjectID: projectID,
    }

    // Encode and send the response
    w.Header().Set("Content-Type", "application/json")
    if err := json.NewEncoder(w).Encode(response); err != nil {
        http.Error(w, "Failed to encode response", http.StatusInternalServerError)
        return
    }
}