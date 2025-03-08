package handlers

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"time"
    "log"

	"backend/internal/db"
)

type ProfileRequest struct {
	SessionToken string `json:"session_token"`
}

type FollowedProject struct {
	ID          int    `json:"id"`
	Name        string `json:"name"`
	Description string `json:"description"`
}

type ProfileResponse struct {
	Username         string            `json:"username"`
	PhoneNumber      string            `json:"phone_number"`
	Email            string            `json:"email"`
	FollowedProjects []FollowedProject `json:"followed_projects"`
}

func GetProfile(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
	var req ProfileRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid request", http.StatusBadRequest)
		return
	}
    log.Printf("Received profile request: %+v", req) 

	var userID int
	var expiresAt time.Time
	err := db.DB.QueryRow("SELECT user_id, expires_at FROM session_tokens WHERE token = $1", req.SessionToken).
		Scan(&userID, &expiresAt)
	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, "Invalid session token", http.StatusUnauthorized)
		} else {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
		}
		return
	}
	if time.Now().After(expiresAt) {
		http.Error(w, "Session token expired", http.StatusUnauthorized)
		return
	}
	var username, phone, email string
	err = db.DB.QueryRow("SELECT username, phone_number, email FROM users WHERE id = $1", userID).
		Scan(&username, &phone, &email)
	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, "User not found", http.StatusNotFound)
		} else {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
		}
		return
	}
	rows, err := db.DB.Query(`
        SELECT p.id, p.name, p.description 
        FROM projects p
        JOIN user_projects up ON p.id = up.project_id
        WHERE up.user_id = $1`, userID)
	if err != nil {
		http.Error(w, "Failed to query followed projects", http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var followedProjects []FollowedProject
	for rows.Next() {
		var project FollowedProject
		if err := rows.Scan(&project.ID, &project.Name, &project.Description); err != nil {
			http.Error(w, "Error scanning followed projects", http.StatusInternalServerError)
			return
		}
		followedProjects = append(followedProjects, project)
	}
	if err = rows.Err(); err != nil {
		http.Error(w, "Error reading followed projects", http.StatusInternalServerError)
		return
	}

	response := ProfileResponse{
		Username:         username,
		PhoneNumber:      phone,
		Email:            email,
		FollowedProjects: followedProjects,
	}
	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(response); err != nil {
		http.Error(w, "Failed to encode response", http.StatusInternalServerError)
		return
	}
}
