package handlers

import (
    _"database/sql"
	"encoding/json"
	"net/http"
    "time"

	"backend/internal/db"
)

type FileDataRequest struct {
	FileID int `json:"file_id"`
}

type VersionInfo struct {
	VersionNumber int    `json:"version_number"`
	CommitMessage string `json:"commit_message"`
    CreatedAt time.Time `json:"created_at"`
}

type FileDataResponse struct {
	FileID              int           `json:"file_id"`
	Content             string        `json:"content"`
    MimeType            string        `json:"mime_type"`
	FileExtension       string        `json:"file_extension"`
	Size                int64         `json:"size"`
	LatestVersionNumber int           `json:"latest_version_number"`
    LatestCreatedAt     time.Time     `json:"latest_created_at"`
	PreviousVersions    []VersionInfo `json:"previous_versions"`
}

func GetFileData(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req FileDataRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}

	fileID := req.FileID
	if fileID <= 0 {
		http.Error(w, "Invalid file_id", http.StatusBadRequest)
		return
	}

	rows, err := db.DB.Query(`
        SELECT version_number, content, mime_type, file_extension, size, commit_message, created_at 
        FROM file_versions 
        WHERE file_id = $1 
        ORDER BY version_number DESC`, fileID)
	if err != nil {
		http.Error(w, "Failed to query file versions", http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var response FileDataResponse
	previousVersions := []VersionInfo{}
	isFirstRow := true

	for rows.Next() {
		var versionNumber int
		var content []byte
		var mimeType, fileExtension, commitMessage string
		var size int64
		var createdAt time.Time

		if err := rows.Scan(&versionNumber, &content, &mimeType, &fileExtension, &size, &commitMessage, &createdAt); err != nil {
			http.Error(w, "Failed to scan file version", http.StatusInternalServerError)
			return
		}

		if isFirstRow {
			response = FileDataResponse{
				FileID:              fileID,
				Content:             string(content),
				MimeType:            mimeType,
				FileExtension:       fileExtension,
				Size:                size,
				LatestVersionNumber: versionNumber,
				LatestCreatedAt:     createdAt,
			}
			isFirstRow = false
		} else {
			previousVersions = append(previousVersions, VersionInfo{
				VersionNumber: versionNumber,
				CommitMessage: commitMessage,
				CreatedAt:     createdAt,
			})
		}
	}
	if err = rows.Err(); err != nil {
		http.Error(w, "Error reading file versions", http.StatusInternalServerError)
		return
	}

	if isFirstRow {
		http.Error(w, "File not found or no versions available", http.StatusNotFound)
		return
	}

	response.PreviousVersions = previousVersions

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(response); err != nil {
		http.Error(w, "Failed to encode response", http.StatusInternalServerError)
	}
}
