package handlers

import (
	"backend/internal/db"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"time"
)

type GetProjectsRequest struct {
	SessionToken string `json:"session_token"`
}

type ProjectsResponse struct {
    ID int `json:"id"`
    Name string `json:"name"`
    Description string `json:"description"`
}

func GetProjects(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req GetProjectsRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}

	var userID int
	err := db.DB.QueryRow("SELECT user_id FROM session_tokens WHERE token = $1", req.SessionToken).Scan(&userID)
	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, "Invalid session token", http.StatusUnauthorized)
			return
		}
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	rows, err := db.DB.Query("SELECT id, name, description FROM projects")
	if err != nil {
		http.Error(w, "Failed to query projects", http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	allProjects := []ProjectsResponse{}
	for rows.Next() {
		var p ProjectsResponse
		if err := rows.Scan(&p.ID, &p.Name, &p.Description); err != nil {
			http.Error(w, "Error scanning projects", http.StatusInternalServerError)
			return
		}
		allProjects = append(allProjects, p)
	}
	if err = rows.Err(); err != nil {
		http.Error(w, "Error reading projects", http.StatusInternalServerError)
		return
	}

	followRows, err := db.DB.Query(`
        SELECT p.id, p.name, p.description 
        FROM projects p
        JOIN user_projects up ON p.id = up.project_id
        WHERE up.user_id = $1`, userID)
	if err != nil {
		http.Error(w, "Failed to query followed projects", http.StatusInternalServerError)
		return
	}
	defer followRows.Close()

	followedProjects := []ProjectsResponse{}
	for followRows.Next() {
		var p ProjectsResponse
		if err := followRows.Scan(&p.ID, &p.Name, &p.Description); err != nil {
			http.Error(w, "Error scanning followed projects", http.StatusInternalServerError)
			return
		}
		followedProjects = append(followedProjects, p)
	}
	if err = followRows.Err(); err != nil {
		http.Error(w, "Error reading followed projects", http.StatusInternalServerError)
		return
	}

	response := map[string]any{
		"projects":          allProjects,
		"followed_projects": followedProjects,
	}

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(response); err != nil {
		http.Error(w, "Failed to encode response", http.StatusInternalServerError)
	}
}
type ViewProjectRequest struct {
	ProjectID    int    `json:"id"`
	SessionToken string `json:"session_token"`
}

type FileResponse struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

type FolderResponse struct {
    ID       int               `json:"id"`
    Name     string            `json:"name"`
    ParentID *int              `json:"parent_id,omitempty"`
    Files    []FileResponse    `json:"files"`
    Folders  []*FolderResponse `json:"folders"` // Ensure this is a slice of pointers
}

type ProjectStructureResponse struct {
	ID          int               `json:"id"`
	Name        string            `json:"name"`
	Description string            `json:"description"`
	Folders     []*FolderResponse `json:"folders"`
    Followers   []string          `json:"followers"`
}

func ViewProject(w http.ResponseWriter, r *http.Request) {
    // Handle preflight OPTIONS request for CORS
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

    // Set CORS headers for the response
    setCORSHeaders(w)

    // Decode the incoming JSON request body
    var req ViewProjectRequest
    if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
        http.Error(w, "Bad request: invalid JSON", http.StatusBadRequest)
        return
    }
    log.Printf("Received project request: %+v", req)

    // Validate the session token
    var tokenUserID int
    var expiresAt time.Time
    err := db.DB.QueryRow("SELECT user_id, expires_at FROM session_tokens WHERE token = $1", req.SessionToken).
        Scan(&tokenUserID, &expiresAt)
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

    // Validate the project ID
    if req.ProjectID <= 0 {
        http.Error(w, "Invalid project id", http.StatusBadRequest)
        return
    }

    // Fetch project details
    var proj struct {
        ID          int
        Name        string
        Description string
    }
    err = db.DB.QueryRow("SELECT id, name, description FROM projects WHERE id = $1", req.ProjectID).
        Scan(&proj.ID, &proj.Name, &proj.Description)
    if err != nil {
        if err == sql.ErrNoRows {
            http.Error(w, "Project not found", http.StatusNotFound)
        } else {
            http.Error(w, "Internal server error", http.StatusInternalServerError)
        }
        return
    }

    // Fetch all folders for the project
    folderRows, err := db.DB.Query("SELECT id, parent_folder_id, name FROM folders WHERE project_id = $1", req.ProjectID)
    if err != nil {
        http.Error(w, "Failed to query folders", http.StatusInternalServerError)
        return
    }
    defer folderRows.Close()

    // Build a map of folders for easy access
    folderMap := make(map[int]*FolderResponse)
    for folderRows.Next() {
        var id int
        var parent sql.NullInt64
        var name string
        if err := folderRows.Scan(&id, &parent, &name); err != nil {
            http.Error(w, "Failed to scan folder", http.StatusInternalServerError)
            return
        }
        var parentID *int
        if parent.Valid {
            p := int(parent.Int64)
            parentID = &p
        }
        folderMap[id] = &FolderResponse{
            ID:       id,
            Name:     name,
            ParentID: parentID,
            Files:    []FileResponse{},
            Folders:  []*FolderResponse{}, // Ensure this matches the struct definition
        }
    }
    if err = folderRows.Err(); err != nil {
        http.Error(w, "Error reading folders", http.StatusInternalServerError)
        return
    }

    // Fetch all files for the project
    fileRows, err := db.DB.Query("SELECT id, folder_id, name FROM files WHERE folder_id IN (SELECT id FROM folders WHERE project_id = $1)", req.ProjectID)
    if err != nil {
        http.Error(w, "Failed to query files", http.StatusInternalServerError)
        return
    }
    defer fileRows.Close()

    // Assign files to their respective folders
    for fileRows.Next() {
        var fid, folderID int
        var fname string
        if err := fileRows.Scan(&fid, &folderID, &fname); err != nil {
            http.Error(w, "Failed to scan file", http.StatusInternalServerError)
            return
        }
        if folder, ok := folderMap[folderID]; ok {
            folder.Files = append(folder.Files, FileResponse{ID: fid, Name: fname})
        }
    }
    if err = fileRows.Err(); err != nil {
        http.Error(w, "Error reading files", http.StatusInternalServerError)
        return
    }

    // Build the folder hierarchy
    var topLevelFolders []*FolderResponse
    for _, folder := range folderMap {
        if folder.ParentID != nil {
            if parentFolder, exists := folderMap[*folder.ParentID]; exists {
                parentFolder.Folders = append(parentFolder.Folders, folder) // Append pointer
            }
        } else {
            topLevelFolders = append(topLevelFolders, folder) // Append pointer
        }
    }

    follow_rows, err:= db.DB.Query(`SELECT u.username
FROM users u
JOIN user_projects up ON u.id = up.user_id
WHERE up.project_id = $1
`, req.ProjectID)

    var followers []string

    for follow_rows.Next() {
        var username string
        if err := follow_rows.Scan(&username); err != nil {
            http.Error(w, "Failed to followers", http.StatusInternalServerError)
            return
        }
        
        followers = append(followers, username)
    }

    // Construct the final response
    response := ProjectStructureResponse{
        ID:          proj.ID,
        Name:        proj.Name,
        Description: proj.Description,
        Folders:     topLevelFolders,
        Followers: followers,
    }

    // Send the JSON response
    w.Header().Set("Content-Type", "application/json")
    if err := json.NewEncoder(w).Encode(response); err != nil {
        http.Error(w, "Failed to encode response", http.StatusInternalServerError)
        return
    }
}