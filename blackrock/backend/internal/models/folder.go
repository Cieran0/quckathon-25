package models

import (
    "time"
)

type Folder struct {
    ID int `json:"id"`
    ProjectID int `json:"project_id"`
    ParentFolderID int `json:"parent_folder_id,omitempty"`
    Name string `json:"name"`
    CreatedAt time.Time `json:"created_at"`
}
