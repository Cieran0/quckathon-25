package handlers

import (
	"backend/internal/db"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"time"
)

type FollowRequest struct {
	ProjectID    int    `json:"id"`
	SessionToken string `json:"session_token"`
}

type FollowResponse struct {
	Success bool `json:"success"`
}

func Follow(w http.ResponseWriter, r *http.Request) {
	// Handle CORS preflight
	if r.Method == http.MethodOptions {
		setCORSHeaders(w)
		return
	}

	// Validate method
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
	setCORSHeaders(w)

	// Decode request
	var req FollowRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid request body", http.StatusBadRequest)
		return
	}

	log.Printf("Received follow request: %+v", req)


	// Validate session token
	var tokenUserID int
	var expiresAt time.Time
	err := db.DB.QueryRow(`
		SELECT user_id, expires_at 
		FROM session_tokens 
		WHERE token = $1
	`, req.SessionToken).Scan(&tokenUserID, &expiresAt)

	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, "Invalid session token", http.StatusUnauthorized)
			return
		}
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	// Check expiration
	if time.Now().After(expiresAt) {
		http.Error(w, "Session token expired", http.StatusUnauthorized)
		return
	}

	// Check if the user is already following the project
	var exists bool
	err = db.DB.QueryRow(`
		SELECT EXISTS(
			SELECT 1 
			FROM user_projects 
			WHERE user_id = $1 AND project_id = $2
		)
	`, tokenUserID, req.ProjectID).Scan(&exists)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	// If the user is already following the project, return success
	if exists {
		response := FollowResponse{
			Success: true,
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(response)
		return
	}

	// Insert into user_projects table
	_, err = db.DB.Exec(`
		INSERT INTO user_projects (user_id, project_id)
		VALUES ($1, $2)
	`, tokenUserID, req.ProjectID)

	if err != nil {
		http.Error(w, "Failed to follow project", http.StatusInternalServerError)
		return
	}

	// Prepare response
	response := FollowResponse{
		Success: true,
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}
