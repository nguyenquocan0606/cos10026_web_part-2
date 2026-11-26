
<header>
    <div class="container">

        <!-- Logo -->
        <a href="index.php" class="Edbridge">
            <span class="brand-logo">
                <img src="img/logo.webp" alt="" width="1024" height="1024">
            </span>
            <span>Edbridge</span>
        </a>

        <!-- Theme switch -->
        <label class="theme-switch">
            <input type="checkbox" id="theme-toggle">
            <span class="slider"></span>
            Light Mode
        </label>

        <!-- Search form -->
        <form class="a1_search" action="search.php" method="get" role="search">
            <label for="a1_search_input">Search jobs</label>

            <div class="a1_search_row">
                <input
                    type="search"
                    id="a1_search_input"
                    name="q"
                    placeholder="Search jobs..."
                    value="<?php
                        echo isset($_GET['q']) && is_string($_GET['q'])
                            ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8')
                            : '';
                    ?>"
                >
                <button type="submit">Search</button>
            </div>
        </form>

        <!-- Navigation -->
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

        <nav aria-label="Main">
            <ul>
                <li>
                    <a href="index.php"
                        <?php if ($currentPage == 'index.php') echo 'aria-current="page"'; ?>>
                        Home
                    </a>
                </li>

                <li>
                    <a href="jobs.php"
                        <?php if ($currentPage == 'jobs.php' || $currentPage == 'search.php') echo 'aria-current="page"'; ?>>
                        Jobs
                    </a>
                </li>

                <li>
                    <a href="apply.html">
                        Apply
                    </a>
                </li>

                <li>
                    <a href="about.html">
                        About us
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</header>
