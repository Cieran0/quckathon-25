package handlers

import (
	"backend/internal/db"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"time"
)

type AnalyticsRequest struct {
    SessionToken     string `json:"session_token"`
    SelectedProjects []int  `json:"selected_projects"`
}

type ProjectAnalytics struct {
    ProjectID    int    `json:"id"`
    ProjectName  string `json:"project_name"`
    Volunteers   int    `json:"volunteers"`
    TotalFunding float64    `json:"total_funding"`
    Followers    int    `json:"followers"`
}

type AnalyticsResponse struct {
    Analytics []ProjectAnalytics `json:"analytics"`
}

func Analytics(w http.ResponseWriter, r *http.Request) {
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
    var req AnalyticsRequest
    if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
        http.Error(w, "Invalid request body", http.StatusBadRequest)
        return
    }

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

    // Validate project selection
    if len(req.SelectedProjects) == 0 {
        http.Error(w, "No projects selected", http.StatusBadRequest)
        return
    }


    // Fetch analytics data
    rows, err := db.DB.Query(`
SELECT 
    p.id, 
    p.name, 
    p.volunteers, 
    COALESCE(SUM(c.amount), 0) AS total_funding, 
    COUNT(up.user_id) AS followers
FROM projects p
LEFT JOIN user_projects up ON up.project_id = p.id
LEFT JOIN contributions c ON c.project_id = p.id
WHERE p.id = ANY($1)
GROUP BY p.id, p.name, p.volunteers;
`, req.SelectedProjects)
    
    if err != nil {
        log.Printf("Database error: %v", err)
        http.Error(w, "Failed to retrieve analytics", http.StatusInternalServerError)
        return
    }
    defer rows.Close()

    var analytics []ProjectAnalytics
    for rows.Next() {
        var p ProjectAnalytics
        if err := rows.Scan(
            &p.ProjectID,
            &p.ProjectName,
            &p.Volunteers,
            &p.TotalFunding,
            &p.Followers,
        ); err != nil {
            log.Printf("Row scan error: %v", err)
            http.Error(w, "Data retrieval error", http.StatusInternalServerError)
            return
        }
        analytics = append(analytics, p)
    }

    // Handle any row iteration errors
    if err = rows.Err(); err != nil {
        log.Printf("Row iteration error: %v", err)
        http.Error(w, "Data processing error", http.StatusInternalServerError)
        return
    }

    // Prepare response
    response := AnalyticsResponse{
        Analytics: analytics,
    }

    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(response)
}
