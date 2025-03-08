package handlers

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"backend/internal/db"
)

type DownloadRequest struct {
	FileID        int `json:"file_id"`
	VersionNumber int `json:"version_number"`
}

func DownloadFile(w http.ResponseWriter, r *http.Request) {
    if r.Method == http.MethodOptions {
		setCORSHeaders(w)
		w.WriteHeader(http.StatusOK)
		return
	}

	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
    setCORSHeaders(w)

	var req DownloadRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}

	if req.FileID <= 0 || req.VersionNumber <= 0 {
		http.Error(w, "Invalid file_id or version_number", http.StatusBadRequest)
		return
	}

	var content []byte
	var mimeType, fileExtension string
	err := db.DB.QueryRow(`
        SELECT content, mime_type, file_extension
        FROM file_versions
        WHERE file_id = $1 AND version_number = $2
        `, req.FileID, req.VersionNumber).Scan(&content, &mimeType, &fileExtension)
	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, "File version not found", http.StatusNotFound)
		} else {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
		}
		return
	}

	w.Header().Set("Content-Type", mimeType)

	filename := fmt.Sprintf("file_%d_v%d.%s", req.FileID, req.VersionNumber, fileExtension)
	w.Header().Set("Content-Disposition", "attachment; filename=\""+filename+"\"")

	w.Write(content)
}
