package models

import (
    "time"
)

type File struct {
    ID int `json:"id"`
    FolderID int `json:"folder_id"`
    Name string `json:"name"`
    CreatedAt time.Time `json:"created_at"`
}
