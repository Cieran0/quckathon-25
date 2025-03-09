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


func makeFolder(project_id int, name string, parent_id int) (int, error) {
    // Start a transaction to ensure atomicity
    tx, err := db.DB.Begin()
    if err != nil {
        return 0, fmt.Errorf("failed to begin transaction: %w", err)
    }
    defer func() {
        if err != nil {
            tx.Rollback()
        }
    }()

    var folderID int
		err = db.DB.QueryRow(
			`INSERT INTO folders (project_id, parent_folder_id, name) VALUES ($1, $2, $3) RETURNING id`,
			project_id, parent_id, name,
		).Scan(&folderID)
		if err != nil {
			return -1, err;
		}
		log.Println("Inserted nested folder for project", name, "ID:", folderID)


    if err := tx.Commit(); err != nil {
        return 0, fmt.Errorf("failed to commit transaction: %w", err)
    }

    return folderID, nil
}

type NewFolderRequest struct {
	ProjectID     int `json:"project_id"`
	SessionToken string `json:"session_token"`
	ParentID     int `json:"parent_id"`
	Name     string `json:"name"`
}

type NewFolderResponse struct {
	FolderID int `json:"id"`
}

func NewFolder(w http.ResponseWriter, r *http.Request) {
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
    var req NewFolderRequest
    if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
        http.Error(w, "Invalid request body", http.StatusBadRequest)
        return
    }
    log.Printf("Received folder request: %+v", req)

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
    projectID, err := makeFolder(req.ProjectID, req.Name, req.ParentID)
    if err != nil {
        http.Error(w, fmt.Sprintf("Failed to create folder: %v", err), http.StatusInternalServerError)
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