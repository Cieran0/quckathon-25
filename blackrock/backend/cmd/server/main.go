package main

import (
	"log"
	"net/http"

	"backend/internal/db"
	"backend/internal/routes"
)

func main() {
    db.Connect()
    //db.InitDatabaseTables()
    //db.TempData()

    router := routes.RegisterRoute()

    log.Println("Listening on 0.0.0.0:8040")
    log.Fatal(http.ListenAndServe("0.0.0.0:8040", router))
}
