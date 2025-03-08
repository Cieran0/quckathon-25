package routes

import (
    "net/http"
    
    "backend/internal/handlers"
)

func RegisterRoute() http.Handler {
    mux := http.NewServeMux()

    mux.HandleFunc("/login", handlers.LoginHandler)
    mux.HandleFunc("/projects", handlers.GetProjects)
    mux.HandleFunc("/project", handlers.ViewProject)
    mux.HandleFunc("/file", handlers.GetFileData)
    mux.HandleFunc("/profile", handlers.GetProfile)

    return mux
}
