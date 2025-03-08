package models

import (
    "time"
)

type FileVersion struct {
    ID int `json:"id"`
    FileID int `json:"file_id"`
    VersionNumber int `json:"version_number"`
    Content []byte `json:"content"`
    MimeType string `json:"mime_type"`
    FileExtension string `json:"file_extension"`
    Size int64 `json:"size"`
    CreatedAt time.Time `json:"created_at"`
}
