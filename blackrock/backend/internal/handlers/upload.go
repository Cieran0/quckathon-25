package handlers

import (
	"database/sql"
	"encoding/json"
	"io"
	"log"
	"net/http"
	"path/filepath"
	"strconv"

	"backend/internal/db"
	"backend/internal/models"
)

type UploadRequest struct {
	SessionToken string `form:"session_token"`
	FileName     string `form:"file_name"`
	FolderID     int    `form:"folder_id"`
	ProjectID    int    `form:"project_id"`
	MimeType     string `form:"mime_type"`
}

type UploadResponse struct {
	FileID        int `json:"file_id"`
	VersionNumber int `json:"version_number"`
}

func UploadFile(w http.ResponseWriter, r *http.Request) {
	// Handle preflight OPTIONS request.
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

	// Parse multipart form with a 10 MB limit.
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		log.Println("Error parsing multipart form:", err)
		http.Error(w, "Error parsing form data", http.StatusBadRequest)
		return
	}

	req := UploadRequest{
		SessionToken: r.FormValue("session_token"),
		FileName:     r.FormValue("file_name"),
		MimeType:     r.FormValue("mime_type"),
	}
	req.FolderID, _ = strconv.Atoi(r.FormValue("folder_id"))
	req.ProjectID, _ = strconv.Atoi(r.FormValue("project_id"))

	log.Printf("Received upload request: %+v", req)


	if req.SessionToken == "" || req.FileName == "" || req.FolderID == 0 || req.ProjectID == 0 {
		log.Println("Missing required parameters")
		http.Error(w, "Missing required parameters", http.StatusBadRequest)
		return
	}

	// Authenticate: check session token.
	var userID int
	err := db.DB.QueryRow("SELECT user_id FROM session_tokens WHERE token = $1", req.SessionToken).Scan(&userID)
	if err != nil {
		log.Println("Session token validation error:", err)
		if err == sql.ErrNoRows {
			http.Error(w, "Unauthorized", http.StatusUnauthorized)
		} else {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
		}
		return
	}

	// Verify folder exists and belongs to project.
	var folderExists bool
	err = db.DB.QueryRow(`
        SELECT EXISTS(
            SELECT 1 FROM folders 
            WHERE id = $1 AND project_id = $2
        )`, req.FolderID, req.ProjectID).Scan(&folderExists)
	if err != nil {
		log.Println("Folder verification error:", err)
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}
	if !folderExists {
		log.Println("Folder does not exist or does not belong to project")
		http.Error(w, "Invalid folder or project", http.StatusBadRequest)
		return
	}

	// Check for existing file in folder.
	var existingFile models.File
	err = db.DB.QueryRow(`
        SELECT id, folder_id, name, created_at 
        FROM files 
        WHERE folder_id = $1 AND name = $2`,
		req.FolderID, req.FileName).Scan(&existingFile.ID, &existingFile.FolderID, &existingFile.Name, &existingFile.CreatedAt)

	// Begin transaction.
	tx, err2 := db.DB.Begin()
	if err2 != nil {
		log.Println("Transaction begin error:", err)
		http.Error(w, "Database transaction error", http.StatusInternalServerError)
		return
	}
	defer tx.Rollback()

	var fileID int
	if err == sql.ErrNoRows {
		// Create new file record.
		err = tx.QueryRow(`
            INSERT INTO files (folder_id, name, created_at) 
            VALUES ($1, $2, NOW()) RETURNING id`,
			req.FolderID, req.FileName).Scan(&fileID)
		if err != nil {
			log.Println("File creation error:", err)
			http.Error(w, "File creation error", http.StatusInternalServerError)
			return
		}
	} else if err != nil {
		log.Println("Error checking for existing file:", err)
		http.Error(w, "Database error checking file existence", http.StatusInternalServerError)
		return
	} else {
		// File exists.
		fileID = existingFile.ID
	}

	// Get next version number.
	var versionNumber int
	err = tx.QueryRow(`
        SELECT COALESCE(MAX(version_number), 0) + 1 
        FROM file_versions 
        WHERE file_id = $1`, fileID).Scan(&versionNumber)
	if err != nil {
		log.Println("Version calculation error:", err)
		http.Error(w, "Version calculation error", http.StatusInternalServerError)
		return
	}

	// Retrieve the uploaded file.
	uploadedFile, _, err := r.FormFile("file")
	if err != nil {
		log.Println("Error retrieving file from form:", err)
		http.Error(w, "Error retrieving file", http.StatusBadRequest)
		return
	}
	defer uploadedFile.Close()

	content, err := io.ReadAll(uploadedFile)
	if err != nil {
		log.Println("Error reading file content:", err)
		http.Error(w, "Error reading file content", http.StatusInternalServerError)
		return
	}

	// Extract file extension.
	fileExtension := filepath.Ext(req.FileName)
	if len(fileExtension) > 0 {
		fileExtension = fileExtension[1:]
	}

	// Insert new file version. Provide an empty commit message.
	_, err = tx.Exec(`
        INSERT INTO file_versions 
        (file_id, version_number, content, mime_type, file_extension, size, commit_message, created_at)
        VALUES ($1, $2, $3, $4, $5, $6, $7, NOW())`,
		fileID, versionNumber, content, req.MimeType, fileExtension, len(content), "")
	if err != nil {
		log.Println("Database insertion error:", err)
		http.Error(w, "Database insertion error", http.StatusInternalServerError)
		return
	}

	if err = tx.Commit(); err != nil {
		log.Println("Transaction commit error:", err)
		http.Error(w, "Database commit error", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(UploadResponse{
		FileID:        fileID,
		VersionNumber: versionNumber,
	})
}

