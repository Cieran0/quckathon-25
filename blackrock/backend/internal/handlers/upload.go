package handlers

import (
	"database/sql"
	"encoding/json"
	"io/ioutil"
	"net/http"
	"path/filepath"
	"strconv"

	"backend/internal/auth"
	"backend/internal/db"
	"backend/internal/models"
)

type UploadRequest struct {
    SessionToken string `form:"session_token"`
    FileName     string `form:"file_name"`
    FolderID     int    `form:"folder_id"`
    ProjectID    int    `form:"project_id"`
    Type         string `form:"type"`
}

type UploadResponse struct {
    FileID        int `json:"file_id"`
    VersionNumber int `json:"version_number"`
}

func UploadFile(w http.ResponseWriter, r *http.Request) {
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

    // Parse multipart form
    err := r.ParseMultipartForm(10 << 20) // 10 MB limit
    if err != nil {
        http.Error(w, "Error parsing form data", http.StatusBadRequest)
        return
    }

    // Parse request parameters
    req := UploadRequest{
        SessionToken: r.FormValue("session_token"),
        FileName:     r.FormValue("file_name"),
        Type:         r.FormValue("type"),
    }

    req.FolderID, _ = strconv.Atoi(r.FormValue("folder_id"))
    req.ProjectID, _ = strconv.Atoi(r.FormValue("project_id"))

    // Validate required parameters
    if req.SessionToken == "" || req.FileName == "" || req.FolderID == 0 || req.ProjectID == 0 {
        http.Error(w, "Missing required parameters", http.StatusBadRequest)
        return
    }

    // Authenticate user
    userID, err := auth.ValidateSession(req.SessionToken)
    if err != nil {
        http.Error(w, "Unauthorized", http.StatusUnauthorized)
        return
    }

    // Verify folder exists and belongs to project
    var folderExists bool
    err = db.DB.QueryRow(`
        SELECT EXISTS(
            SELECT 1 FROM folders 
            WHERE id = $1 AND project_id = $2
        )`, req.FolderID, req.ProjectID).Scan(&folderExists)
    if err != nil || !folderExists {
        http.Error(w, "Invalid folder or project", http.StatusBadRequest)
        return
    }

    // Check for existing file in folder
    var existingFile models.File
    err = db.DB.QueryRow(`
        SELECT * FROM files 
        WHERE folder_id = $1 AND name = $2`,
        req.FolderID, req.FileName).Scan(&existingFile.ID, &existingFile.FolderID, &existingFile.Name, &existingFile.CreatedAt)

    tx, err := db.DB.Begin()
    if err != nil {
        http.Error(w, "Database transaction error", http.StatusInternalServerError)
        return
    }
    defer tx.Rollback()

    // Handle file creation/versioning
    var fileID int
    if err == sql.ErrNoRows {
        // Create new file
        err = tx.QueryRow(`
            INSERT INTO files (folder_id, name, created_at) 
            VALUES ($1, $2, NOW()) RETURNING id`,
            req.FolderID, req.FileName).Scan(&fileID)
        if err != nil {
            http.Error(w, "File creation error", http.StatusInternalServerError)
            return
        }
    } else {
        // Use existing file
        fileID = existingFile.ID
    }

    // Get next version number
    var versionNumber int
    err = tx.QueryRow(`
        SELECT COALESCE(MAX(version_number), 0) + 1 
        FROM file_versions 
        WHERE file_id = $1`, fileID).Scan(&versionNumber)
    if err != nil {
        http.Error(w, "Version calculation error", http.StatusInternalServerError)
        return
    }

    // Read uploaded file
    uploadedFile, _, err := r.FormFile("file")
    if err != nil {
        http.Error(w, "Error retrieving file", http.StatusBadRequest)
        return
    }
    defer uploadedFile.Close()

    content, err := ioutil.ReadAll(uploadedFile)
    if err != nil {
        http.Error(w, "Error reading file content", http.StatusInternalServerError)
        return
    }

    // Extract file extension
    fileExtension := filepath.Ext(req.FileName)
    if len(fileExtension) > 0 {
        fileExtension = fileExtension[1:] // Remove leading dot
    }

    // Insert new file version
    _, err = tx.Exec(`
        INSERT INTO file_versions 
        (file_id, version_number, content, mime_type, file_extension, size, created_at)
        VALUES ($1, $2, $3, $4, $5, $6, NOW())`,
        fileID, versionNumber, content, req.Type, fileExtension, len(content))
    if err != nil {
        http.Error(w, "Database insertion error", http.StatusInternalServerError)
        return
    }

    tx.Commit()

    w.WriteHeader(http.StatusCreated)
    json.NewEncoder(w).Encode(UploadResponse{
        FileID:        fileID,
        VersionNumber: versionNumber,
    })
}

func setCORSHeaders(w http.ResponseWriter) {
    w.Header().Set("Access-Control-Allow-Origin", "*")
    w.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
    w.Header().Set("Access-Control-Allow-Headers", "Content-Type")
}