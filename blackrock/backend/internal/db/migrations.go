package db

import (
	"log"

	"golang.org/x/crypto/bcrypt"
)

// InitDatabaseTables creates all necessary tables, including new columns for volunteers and total funding in the projects table.
func InitDatabaseTables() {
	createProjectTable := `
    CREATE TABLE IF NOT EXISTS projects(
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        description TEXT,
        volunteers INT DEFAULT 0,
        total_funding NUMERIC(12,2) DEFAULT 0.00,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
    );
    `
	_, err := DB.Exec(createProjectTable)
	if err != nil {
		log.Fatal("Failed to create projects table:", err)
	}
	log.Println("Created projects table")

	createFoldersTable := `
    CREATE TABLE IF NOT EXISTS folders (
        id SERIAL PRIMARY KEY,
        project_id INT NOT NULL,
        parent_folder_id INT,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_project
            FOREIGN KEY (project_id)
            REFERENCES projects (id)
            ON DELETE CASCADE,
        CONSTRAINT fk_parent_folder
            FOREIGN KEY (parent_folder_id)
            REFERENCES folders (id)
            ON DELETE CASCADE
    );
    `
	_, err = DB.Exec(createFoldersTable)
	if err != nil {
		log.Fatal("Failed to create folders table:", err)
	}
	log.Println("Created folders table")

	createFilesTable := `
    CREATE TABLE IF NOT EXISTS files (
        id SERIAL PRIMARY KEY,
        folder_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_folder
            FOREIGN KEY (folder_id)
            REFERENCES folders (id)
            ON DELETE CASCADE
    );
    `
	_, err = DB.Exec(createFilesTable)
	if err != nil {
		log.Fatal("Error creating files table:", err)
	}
	log.Println("Created files table")

	createFileVersionsTable := `
    CREATE TABLE IF NOT EXISTS file_versions (
        id SERIAL PRIMARY KEY,
        file_id INT NOT NULL,
        version_number INT NOT NULL,
        content BYTEA,
        mime_type VARCHAR(255),
        file_extension VARCHAR(10),
        size BIGINT,
        commit_message TEXT,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_file
            FOREIGN KEY (file_id)
            REFERENCES files (id)
            ON DELETE CASCADE,
        CONSTRAINT unique_file_version UNIQUE (file_id, version_number)
    );
    `
	_, err = DB.Exec(createFileVersionsTable)
	if err != nil {
		log.Fatal("Failed to create file versions table:", err)
	}
	log.Println("Created file versions table")

	createUsersTable := `
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        hashed_password TEXT NOT NULL,
        phone_number VARCHAR(20),
        email VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
    );
    `
	_, err = DB.Exec(createUsersTable)
	if err != nil {
		log.Fatal("Failed to create users table:", err)
	}
	log.Println("Created users table")

	createUserProjectsTable := `
    CREATE TABLE IF NOT EXISTS user_projects (
        user_id INT NOT NULL,
        project_id INT NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, project_id),
        CONSTRAINT fk_user
            FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE CASCADE,
        CONSTRAINT fk_project_followed
            FOREIGN KEY (project_id)
            REFERENCES projects (id)
            ON DELETE CASCADE
    );
    `
	_, err = DB.Exec(createUserProjectsTable)
	if err != nil {
		log.Fatal("Failed to create user projects table:", err)
	}
	log.Println("Created user projects table")

	createSessionTokensTable := `
    CREATE TABLE IF NOT EXISTS session_tokens (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP WITH TIME ZONE,
        CONSTRAINT fk_user
            FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE CASCADE
    );
    `
	_, err = DB.Exec(createSessionTokensTable)
	if err != nil {
		log.Fatal("Failed to create session tokens table:", err)
	}
	log.Println("Created session tokens table")
}

// TempData inserts sample projects, folders, files, file versions, users, and user-project relationships.
// The projects now include a number of volunteers and total funding values.
func TempData() {
	var projectIDs []int

	// Updated projects data with volunteers and total funding values.
	projects := []struct {
		name         string
		description  string
		volunteers   int
		totalFunding float64
	}{
		{"Project Alpha", "Description for Project Alpha", 10, 1000.00},
		{"Project Beta", "Description for Project Beta", 5, 500.00},
		{"Project Gamma", "Description for Project Gamma", 15, 1500.00},
		{"Project Delta", "Description for Project Delta", 8, 800.00},
		{"Project Epsilon", "Description for Project Epsilon", 20, 2000.00},
	}

	for _, proj := range projects {
		var projectID int
		err := DB.QueryRow(
			`INSERT INTO projects (name, description, volunteers, total_funding) VALUES ($1, $2, $3, $4) RETURNING id`,
			proj.name, proj.description, proj.volunteers, proj.totalFunding,
		).Scan(&projectID)
		if err != nil {
			log.Fatal("Failed to insert project:", err)
		}
		projectIDs = append(projectIDs, projectID)
		log.Println("Inserted project:", proj.name, "ID:", projectID)

		var rootFolderID int
		err = DB.QueryRow(
			`INSERT INTO folders (project_id, name) VALUES ($1, $2) RETURNING id`,
			projectID, "Root Folder",
		).Scan(&rootFolderID)
		if err != nil {
			log.Fatal("Failed to insert root folder for project", proj.name, ":", err)
		}
		log.Println("Inserted root folder for project", proj.name, "ID:", rootFolderID)

		var nestedFolderID int
		err = DB.QueryRow(
			`INSERT INTO folders (project_id, parent_folder_id, name) VALUES ($1, $2, $3) RETURNING id`,
			projectID, rootFolderID, "Nested Folder",
		).Scan(&nestedFolderID)
		if err != nil {
			log.Fatal("Failed to insert nested folder for project", proj.name, ":", err)
		}
		log.Println("Inserted nested folder for project", proj.name, "ID:", nestedFolderID)

		var rootFileID int
		err = DB.QueryRow(
			`INSERT INTO files (folder_id, name) VALUES ($1, $2) RETURNING id`,
			rootFolderID, "root_file.txt",
		).Scan(&rootFileID)
		if err != nil {
			log.Fatal("Failed to insert file in root folder for project", proj.name, ":", err)
		}
		log.Println("Inserted file in root folder for project", proj.name, "ID:", rootFileID)

		rootContent := "Placeholder content for root_file.txt in " + proj.name
		_, err = DB.Exec(
			`INSERT INTO file_versions (file_id, version_number, content, mime_type, file_extension, size, commit_message)
			 VALUES ($1, $2, $3, $4, $5, $6, $7)`,
			rootFileID, 1, []byte(rootContent), "text/plain", "txt", len(rootContent), "Initial version",
		)
		if err != nil {
			log.Fatal("Failed to insert file version for root file in project", proj.name, ":", err)
		}
		log.Println("Inserted file version for root file in project", proj.name)

		var nestedFileID int
		err = DB.QueryRow(
			`INSERT INTO files (folder_id, name) VALUES ($1, $2) RETURNING id`,
			nestedFolderID, "nested_file.txt",
		).Scan(&nestedFileID)
		if err != nil {
			log.Fatal("Failed to insert file in nested folder for project", proj.name, ":", err)
		}
		log.Println("Inserted file in nested folder for project", proj.name, "ID:", nestedFileID)

		nestedContent := "Placeholder content for nested_file.txt in " + proj.name
		_, err = DB.Exec(
			`INSERT INTO file_versions (file_id, version_number, content, mime_type, file_extension, size, commit_message)
			 VALUES ($1, $2, $3, $4, $5, $6, $7)`,
			nestedFileID, 1, []byte(nestedContent), "text/plain", "txt", len(nestedContent), "Initial version",
		)
		if err != nil {
			log.Fatal("Failed to insert file version for nested file in project", proj.name, ":", err)
		}
		log.Println("Inserted file version for nested file in project", proj.name)
	}

	type userData struct {
		username, password, phone, email string
	}
	users := []userData{
		{"alice", "password123", "1111111111", "alice@example.com"},
		{"bob", "password123", "2222222222", "bob@example.com"},
		{"carol", "password123", "3333333333", "carol@example.com"},
	}

	userIDs := make(map[string]int)
	for _, u := range users {
		hashedPasswordBytes, err := bcrypt.GenerateFromPassword([]byte(u.password), bcrypt.DefaultCost)
		if err != nil {
			log.Fatal("Failed to hash password for user", u.username, ":", err)
		}
		hashedPassword := string(hashedPasswordBytes)

		var userID int
		err = DB.QueryRow(
			`INSERT INTO users (username, hashed_password, phone_number, email) VALUES ($1, $2, $3, $4) RETURNING id`,
			u.username, hashedPassword, u.phone, u.email,
		).Scan(&userID)
		if err != nil {
			log.Fatal("Failed to insert user", u.username, ":", err)
		}
		userIDs[u.username] = userID
		log.Println("Inserted user", u.username, "with ID:", userID)
	}

	if len(projectIDs) >= 5 {
		_, err := DB.Exec(`INSERT INTO user_projects (user_id, project_id) VALUES ($1, $2)`,
			userIDs["alice"], projectIDs[0])
		if err != nil {
			log.Fatal("Failed to insert user_projects for alice:", err)
		}
		_, err = DB.Exec(`INSERT INTO user_projects (user_id, project_id) VALUES ($1, $2)`,
			userIDs["alice"], projectIDs[2])
		if err != nil {
			log.Fatal("Failed to insert user_projects for alice:", err)
		}

		_, err = DB.Exec(`INSERT INTO user_projects (user_id, project_id) VALUES ($1, $2)`,
			userIDs["bob"], projectIDs[1])
		if err != nil {
			log.Fatal("Failed to insert user_projects for bob:", err)
		}
		_, err = DB.Exec(`INSERT INTO user_projects (user_id, project_id) VALUES ($1, $2)`,
			userIDs["bob"], projectIDs[3])
		if err != nil {
			log.Fatal("Failed to insert user_projects for bob:", err)
		}

		_, err = DB.Exec(`INSERT INTO user_projects (user_id, project_id) VALUES ($1, $2)`,
			userIDs["carol"], projectIDs[4])
		if err != nil {
			log.Fatal("Failed to insert user_projects for carol:", err)
		}
		log.Println("Inserted user-project follow relationships")
	} else {
		log.Println("Not enough projects inserted to create user-project relationships")
	}
}
