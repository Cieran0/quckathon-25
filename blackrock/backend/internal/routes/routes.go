package routes

import (
    "net/http"
    
    "backend/internal/handlers"
)

func RegisterRoute() http.Handler {
    mux := http.NewServeMux()

    mux.HandleFunc("/login", handlers.LoginHandler)

    return mux
}
