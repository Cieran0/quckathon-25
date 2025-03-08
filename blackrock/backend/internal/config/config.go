package config

import (
    "fmt"
)

func GetDatabaseURL() string {
    host := "localhost"
    port := 5432
    user := "bennh"
    password := "houghton"
    dbname := "blackrock"
    sslmode := "disable"

    return fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s sslmode=%s", host, port, user, password, dbname, sslmode)
}
