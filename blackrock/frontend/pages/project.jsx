import Head from 'next/head';

export default function ProjectsPage() {
  return (
    <>
      <Head>
        <title>Projects</title>
        <meta charSet="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link
          href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
          rel="stylesheet"
        />
      </Head>

      <div className="bg-white flex flex-col h-screen">
        <header className="flex items-center justify-between p-4 bg-white header-bottom-border">
          <img src="/logo.png" alt="Logo" className="h-10" />
          <nav className="flex items-center">
            <a href="#projects" className="mr-4 text-custom-green hover:text-black">
              Projects
            </a>
            <a href="#analytics" className="mr-4 text-custom-green hover:text-black">
              Analytics
            </a>
            <button className="px-4 py-2 rounded text-white bg-green-600 hover:bg-green-400">
              Login
            </button>
          </nav>
        </header>

        <main className="flex flex-grow">
          <aside className="w-64 p-4 custom-green text-white">
            <h2 className="text-lg mb-2">Followed Projects</h2>
            <ul>
              <li className="mb-2">
                <a href="#" className="text-white hover:text-gray-300">
                  Project 1
                </a>
              </li>
              <li>
                <a href="#" className="text-white hover:text-gray-300">
                  Project 2
                </a>
              </li>
            </ul>
          </aside>

          <section className="flex-grow p-4">
            <div className="flex items-center mb-4">
              <input
                type="text"
                placeholder="Search Projects..."
                className="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300"
              />
              <button className="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">
                Search
              </button>
            </div>
            <div className="grid grid-cols-4 gap-4">
              <div className="project-card">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                  />
                </svg>
                <h3>Project name</h3>
                <p>Description text</p>
              </div>
              <div className="new-project-card">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="white"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                >
                  <path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z" />
                </svg>
                <h3>Add Project</h3>
                <p>Create a new project</p>
              </div>
            </div>
          </section>

          <aside className="w-64 p-4 custom-green text-white">
            <h2 className="text-lg mb-2">Filters</h2>

            <div className="mb-4">
              <label className="inline-block mb-2 text-white">Show Only Followed</label>
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="show-followed"
                  className="mr-2 text-green-600 focus:ring-green-500"
                />
                <label htmlFor="show-followed" className="text-white">
                  On
                </label>
              </div>
            </div>

            <div className="mb-4">
              <label className="block mb-2 text-white">Volunteer Count</label>
              <div className="flex flex-col">
                <button className="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">
                  Most Volunteers
                </button>
                <button className="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">
                  Fewest Volunteers
                </button>
              </div>
            </div>

            <div className="mb-4">
              <label className="block mb-2 text-white">Activity Level</label>
              <div className="flex flex-col">
                <button className="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">
                  Recently Updated
                </button>
                <button className="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">
                  Least Active
                </button>
              </div>
            </div>

            <div className="mb-4">
              <label className="block mb-2 text-white">Category</label>
              <select className="w-full px-3 py-2 rounded border border-gray-300 bg-white text-black">
                <option value="all">All</option>
                <option value="community">Community</option>
                <option value="tech">Tech</option>
                <option value="education">Education</option>
              </select>
            </div>

            <button className="w-full px-4 py-2 mt-4 rounded bg-green-600 text-white hover:bg-green-400">
              Apply Filters
            </button>
          </aside>
        </main>

        <footer className="dark-gray text-white py-4 text-center">
          <p className="text-sm">&copy; 2024 The Green Team. All rights reserved.</p>
        </footer>

        <style jsx global>{`
          .custom-green {
            background-color: #006844 !important;
          }
          .text-custom-green {
            color: #006844 !important;
          }
          .dark-gray {
            background-color: #333 !important;
          }
          .header-bottom-border {
            border-bottom: 2px solid #333;
          }
          .project-card {
            background-color: #e0e0e0;
            padding: 1rem;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          }
          .project-card:hover {
            background-color: #a9a9a9;
          }
          .project-card svg {
            display: block;
            margin: 0 auto 1rem auto;
            width: 30px;
            height: 30px;
          }
          .project-card h3 {
            margin-top: 0;
            font-size: 1rem;
          }
          .project-card p {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #666;
          }
          .new-project-card {
            background-color: #006844;
            padding: 1rem;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          }
          .new-project-card:hover {
            background-color: #00a169;
          }
          .new-project-card svg {
            display: block;
            margin: 0 auto 1rem auto;
            width: 30px;
            height: 30px;
            color: white;
          }
          .new-project-card h3 {
            margin-top: 0;
            font-size: 1rem;
            color: white;
          }
          .new-project-card p {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #c0c0c0;
          }
        `}</style>
      </div>
    </>
  );
}