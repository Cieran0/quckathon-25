package main

import (
    "net/http"
    "log"

    "backend/internal/db"
    "backend/internal/routes"
)

func main() {
    db.Connect()
    db.InitDatabaseTables()
    db.TempData()

    router := routes.RegisterRoute()

    log.Println("Listening on 10.201.121.182:8000")
    log.Fatal(http.ListenAndServe("10.201.121.182:8000", router))
}
