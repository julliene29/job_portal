

<?php



session_start();



require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../classes/jobss.php';



$database = new Database();

$db = $database->connect();



$jobModel = new Jobs($db);



// Search and filter values

$search = trim($_GET['search'] ?? '');

$type = $_GET['type'] ?? '';



$minSalaryInput = $_GET['min_salary'] ?? '';

$maxSalaryInput = $_GET['max_salary'] ?? '';



$minSalary = is_numeric($minSalaryInput)

    ? max(0, (float) $minSalaryInput)

    : 0;



$maxSalary = (

    $maxSalaryInput !== '' &&

    is_numeric($maxSalaryInput)

)

    ? (float) $maxSalaryInput

    : null;



// Get jobs

$jobs = $jobModel->getAll(

    $search,

    $type,

    $minSalary,

    $maxSalary

);



$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

$isJobSeeker = ($_SESSION['role'] ?? '') === 'job_seeker';



?>



<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <title>Job Portal | Find Your Dream Job</title>



    <style>

        /* GENERAL */

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;

        }



        :root {

            --primary: #173b67;

            --blue: #2563eb;

            --light-blue: #eff6ff;

            --white: #ffffff;

            --background: #f4f7fc;

            --text: #1e293b;

            --muted: #64748b;

            --border: #e2e8f0;

            --green: #15803d;

            --red: #b91c1c;

        }



        body {

            font-family: Arial, Helvetica, sans-serif;

            background: var(--background);

            color: var(--text);

            line-height: 1.6;

        }



        a {

            text-decoration: none;

        }



        button,

        input,

        select {

            font: inherit;

        }



        /* HEADER */

        header {

            background: var(--primary);

            color: var(--white);

            padding: 20px 6%;

        }



        .navbar {

            max-width: 1200px;

            margin: auto;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            flex-wrap: wrap;

        }



        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            color: var(--white);

        }



        .brand-icon {

            width: 46px;

            height: 46px;

            background: var(--white);

            color: var(--primary);

            border-radius: 12px;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 24px;

            font-weight: bold;

        }



        .brand h1 {

            font-size: 25px;

            line-height: 1.3;

        }



        .brand p {

            color: #dbeafe;

            font-size: 12px;

        }



        nav {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px;

        }



        nav a {

            color: var(--white);

            padding: 9px 12px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: 600;

            transition: background 0.2s;

        }



        nav a:hover {

            background: rgba(255, 255, 255, 0.14);

        }



        nav .nav-register {

            background: var(--white);

            color: var(--primary);

        }



        nav .nav-register:hover {

            background: #dbeafe;

        }



        /* HERO */

        .hero {

            background: linear-gradient(

                135deg,

                #173b67 0%,

                #245da1 100%

            );

            color: var(--white);

            padding: 65px 6% 80px;

        }



        .hero-content {

            max-width: 1000px;

            margin: auto;

            text-align: center;

        }



        .hero-label {

            display: inline-block;

            background: rgba(255, 255, 255, 0.13);

            border: 1px solid rgba(255, 255, 255, 0.25);

            border-radius: 30px;

            padding: 6px 15px;

            margin-bottom: 18px;

            font-size: 13px;

        }



        .hero h2 {

            color: var(--white);

            font-size: clamp(30px, 5vw, 48px);

            line-height: 1.2;

            margin-bottom: 18px;

        }



        .hero h2 span {

            color: #bfdbfe;

        }



        .hero-description {

            max-width: 650px;

            margin: 0 auto;

            color: #e2eaf7;

            font-size: 16px;

        }



        /* MAIN CONTENT */

        main {

            max-width: 1150px;

            width: 90%;

            margin: -30px auto 60px;

            position: relative;

        }



        /* SEARCH PANEL */

        .search-section {

            background: var(--white);

            padding: 28px;

            border: 1px solid var(--border);

            border-radius: 15px;

            box-shadow: 0 12px 35px rgba(23, 59, 103, 0.10);

            margin-bottom: 45px;

        }



        .search-section h2 {

            color: var(--primary);

            font-size: 21px;

            margin-bottom: 20px;

        }



        .search-form {

            display: grid;

            grid-template-columns: minmax(180px, 2fr)

                                   minmax(130px, 1fr)

                                   minmax(130px, 1fr)

                                   minmax(130px, 1fr)

                                   auto;

            gap: 12px;

            align-items: end;

        }



        .form-group {

            min-width: 0;

        }



        .form-group label {

            display: block;

            font-size: 13px;

            font-weight: 700;

            color: #334155;

            margin-bottom: 7px;

        }



        .form-group input,

        .form-group select {

            display: block;

            width: 100%;

            min-width: 0;

            height: 45px;

            padding: 10px 12px;

            background: var(--white);

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            color: var(--text);

            font-size: 13px;

        }



        .form-group input:focus,

        .form-group select:focus {

            outline: 2px solid #bfdbfe;

            border-color: var(--blue);

        }



        .search-actions {

            display: flex;

            gap: 8px;

            align-items: center;

        }



        .btn {

            display: inline-flex;

            justify-content: center;

            align-items: center;

            min-height: 43px;

            padding: 10px 17px;

            border: none;

            border-radius: 8px;

            font-weight: 700;

            font-size: 13px;

            cursor: pointer;

            transition: background 0.2s, transform 0.2s;

            text-align: center;

        }



        .btn-primary {

            background: var(--blue);

            color: var(--white);

        }



        .btn-primary:hover {

            background: var(--primary);

            transform: translateY(-1px);

        }



        .btn-light {

            background: var(--light-blue);

            color: var(--primary);

        }



        .btn-light:hover {

            background: #dbeafe;

        }



        .btn:disabled {

            background: #cbd5e1;

            color: #475569;

            cursor: not-allowed;

            transform: none;

        }



        /* JOB LIST HEADING */

        .jobs-heading {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            flex-wrap: wrap;

            margin-bottom: 22px;

        }



        .jobs-heading h2 {

            color: var(--primary);

            font-size: 27px;

        }



        .jobs-heading p {

            color: var(--muted);

            font-size: 14px;

        }



        /* JOB CARDS */

        .job-card {

            background: var(--white);

            border: 1px solid var(--border);

            border-radius: 14px;

            padding: 25px;

            margin-bottom: 18px;

            box-shadow: 0 4px 15px rgba(23, 59, 103, 0.04);

            transition: box-shadow 0.2s, transform 0.2s;

        }



        .job-card:hover {

            box-shadow: 0 10px 28px rgba(23, 59, 103, 0.09);

            transform: translateY(-2px);

        }



        .job-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 18px;

            margin-bottom: 18px;

        }



        .job-title-area {

            display: flex;

            align-items: flex-start;

            gap: 15px;

            min-width: 0;

        }



        .job-icon {

            width: 54px;

            height: 54px;

            flex-shrink: 0;

            border-radius: 12px;

            background: var(--light-blue);

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

        }



        .job-card h3 {

            color: var(--primary);

            font-size: 21px;

            margin-bottom: 5px;

            overflow-wrap: anywhere;

        }



        .job-subtitle {

            color: var(--muted);

            font-size: 13px;

        }



        .status-badge {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;

        }



        .status-open {

            background: #dcfce7;

            color: #166534;

        }



        .status-closed {

            background: #fee2e2;

            color: #991b1b;

        }



        .job-description {

            color: #475569;

            font-size: 14px;

            margin-bottom: 20px;

            overflow-wrap: anywhere;

        }



        .job-details {

            display: flex;

            flex-wrap: wrap;

            gap: 12px;

            margin-bottom: 20px;

        }



        .detail-item {

            background: #f8fafc;

            border: 1px solid #edf2f7;

            border-radius: 8px;

            padding: 10px 13px;

            font-size: 13px;

        }



        .detail-item strong {

            color: var(--primary);

        }



        .job-meta {

            display: flex;

            flex-wrap: wrap;

            gap: 10px 25px;

            border-top: 1px solid var(--border);

            padding-top: 16px;

            margin-bottom: 18px;

        }



        .meta-item {

            font-size: 12px;

            color: var(--muted);

            overflow-wrap: anywhere;

        }



        .meta-item strong {

            color: #334155;

        }



        .job-footer {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            flex-wrap: wrap;

        }



        .job-footer p {

            color: var(--muted);

            font-size: 13px;

        }



        .closed {

            color: var(--red) !important;

            font-weight: 700;

        }



        .open {

            color: var(--green) !important;

            font-weight: 700;

        }



        /* EMPTY RESULTS / ERRORS */

        .message-box {

            background: var(--white);

            padding: 30px 20px;

            border-radius: 12px;

            border: 1px solid var(--border);

            text-align: center;

            color: var(--muted);

        }



        .message-box h3 {

            color: var(--primary);

            margin-bottom: 8px;

        }



        /* FOOTER */

        footer {

            background: var(--primary);

            color: #dbeafe;

            text-align: center;

            padding: 24px 15px;

            font-size: 13px;

        }



        /* TABLET */

        @media (max-width: 1000px) {

            .search-form {

                grid-template-columns: repeat(2, minmax(0, 1fr));

            }



            .search-actions {

                align-self: end;

            }

        }



        /* MOBILE */

        @media (max-width: 600px) {

            header {

                padding: 18px 5%;

            }



            .navbar {

                align-items: flex-start;

                flex-direction: column;

            }



            .brand h1 {

                font-size: 22px;

            }



            nav {

                gap: 6px;

            }



            nav a {

                padding: 8px 9px;

                font-size: 12px;

            }



            .hero {

                padding: 45px 6% 65px;

            }



            .hero-description {

                font-size: 14px;

            }



            main {

                width: 92%;

                margin-top: -22px;

            }



            .search-section {

                padding: 20px;

                margin-bottom: 32px;

            }



            .search-form {

                grid-template-columns: 1fr;

            }



            .search-actions {

                flex-direction: column;

                align-items: stretch;

            }



            .search-actions .btn {

                width: 100%;

            }



            .jobs-heading h2 {

                font-size: 23px;

            }



            .job-card {

                padding: 18px;

            }



            .job-top {

                flex-direction: column;

                gap: 12px;

            }



            .job-card h3 {

                font-size: 19px;

            }



            .job-details {

                flex-direction: column;

                gap: 8px;

            }



            .detail-item {

                width: 100%;

            }



            .job-footer {

                align-items: stretch;

                flex-direction: column;

            }



            .job-footer .btn {

                width: 100%;

            }

        }

    </style>

</head>



<body>



<header>

    <div class="navbar">



        <a href="index.php" class="brand">

            <div class="brand-icon">J</div>



            <div>

                <h1>Job Portal</h1>

                <p>Your future starts here</p>

            </div>

        </a>



        <nav>
            <a href="index.php">Home</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-user">
                    Welcome, <?= htmlspecialchars(
                        $_SESSION['name'] ?? 'User',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>

                <?php if ($isJobSeeker): ?>
                    <a href="my_applications.php">My Applications</a>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <a href="create.php">Post a Job</a>
                    <a href="view_applications.php">Applications</a>
                    <a href="manage_jobs.php">Manage Jobs</a>
                <?php endif; ?>

                <a href="logout.php" class="nav-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-login">Login</a>
                <a href="register.php" class="nav-register">Registration</a>
            <?php endif; ?>
        </nav>



    </div>

</header>



<section class="hero">

    <div class="hero-content">



        <div class="hero-label">Find opportunities. Build your future.</div>



        <h2>

            Find Your <span>Dream Job</span>

        </h2>



        <p class="hero-description">

            Discover job opportunities that match your skills,

            experience and ambitions. Your next career move starts here.

        </p>



    </div>

</section>



<main>



    <!-- SEARCH AND FILTERS -->

    <section class="search-section">



        <h2>Find the Right Opportunity</h2>



        <form action="index.php" method="GET" class="search-form">



            <div class="form-group">

                <label for="search">Job title or keyword</label>



                <input

                    type="text"

                    id="search"

                    name="search"

                    placeholder="e.g. Teacher, Cashier"

                    value="<?= htmlspecialchars(

                        $search,

                        ENT_QUOTES,

                        'UTF-8'

                    ) ?>"

                >

            </div>



            <div class="form-group">

                <label for="type">Job type</label>



                <select name="type" id="type">

                    <option value="">All types</option>



                    <option

                        value="on-site"

                        <?= $type === 'on-site' ? 'selected' : '' ?>

                    >

                        On-site

                    </option>



                    <option

                        value="remote"

                        <?= $type === 'remote' ? 'selected' : '' ?>

                    >

                        Remote

                    </option>

                </select>

            </div>



            <div class="form-group">

                <label for="min_salary">Minimum salary</label>



                <input

                    type="number"

                    id="min_salary"

                    name="min_salary"

                    min="0"

                    placeholder="Minimum"

                    value="<?= htmlspecialchars(

                        $minSalaryInput,

                        ENT_QUOTES,

                        'UTF-8'

                    ) ?>"

                >

            </div>



            <div class="form-group">

                <label for="max_salary">Maximum salary</label>



                <input

                    type="number"

                    id="max_salary"

                    name="max_salary"

                    min="0"

                    placeholder="Maximum"

                    value="<?= htmlspecialchars(

                        $maxSalaryInput,

                        ENT_QUOTES,

                        'UTF-8'

                    ) ?>"

                >

            </div>



            <div class="search-actions">

                <button type="submit" class="btn btn-primary">

                    Search Jobs

                </button>



                <a href="index.php" class="btn btn-light">

                    Clear

                </a>

            </div>



        </form>



    </section>



    <!-- JOB LIST -->

    <section>



        <div class="jobs-heading">

            <div>

                <h2>Latest Opportunities</h2>

                <p>Explore available jobs and take the next step.</p>

            </div>

        </div>



        <?php if (

            $maxSalary !== null &&

            $maxSalary < $minSalary

        ): ?>



            <div class="message-box">

                <h3>Invalid salary range</h3>

                <p>

                    Maximum salary must be greater than or equal

                    to minimum salary.

                </p>

            </div>



        <?php elseif (empty($jobs)): ?>



            <div class="message-box">

                <h3>No jobs found</h3>

                <p>

                    No jobs match your search. Try changing your filters.

                </p>

            </div>



        <?php else: ?>



            <?php foreach ($jobs as $job): ?>



                <?php

                $deadlineTimestamp = !empty($job['deadline'])

                    ? strtotime($job['deadline'])

                    : false;



                $isClosed = (

                    strtolower($job['status'] ?? '') === 'closed'

                    ||

                    (

                        $deadlineTimestamp !== false

                        && $deadlineTimestamp <= time()

                    )

                );

                ?>



                <article class="job-card">



                    <div class="job-top">



                        <div class="job-title-area">



                            <div class="job-icon">▣</div>



                            <div>

                                <h3>

                                    <?= htmlspecialchars(

                                        $job['title'],

                                        ENT_QUOTES,

                                        'UTF-8'

                                    ) ?>

                                </h3>



                                <p class="job-subtitle">

                                    Job opportunity

                                </p>

                            </div>



                        </div>



                        <span class="status-badge <?= $isClosed

                            ? 'status-closed'

                            : 'status-open' ?>">

                            <?= $isClosed ? 'Closed' : 'Open' ?>

                        </span>



                    </div>



                    <div class="job-description">

                        <?= nl2br(htmlspecialchars(

                            $job['description'],

                            ENT_QUOTES,

                            'UTF-8'

                        )) ?>

                    </div>



                    <div class="job-details">



                        <div class="detail-item">

                            <strong>Salary:</strong>

                            <?= htmlspecialchars(

                                (string) $job['salary'],

                                ENT_QUOTES,

                                'UTF-8'

                            ) ?>

                        </div>



                        <div class="detail-item">

                            <strong>Job Type:</strong>

                            <?= htmlspecialchars(

                                $job['type'],

                                ENT_QUOTES,

                                'UTF-8'

                            ) ?>

                        </div>



                    </div>



                    <div class="job-meta">



                        <div class="meta-item">

                            <strong>Posted on:</strong>



                            <?php if (!empty($job['created_at'])): ?>

                                <?= date(

                                    'd M Y, h:i A',

                                    strtotime($job['created_at'])

                                ) ?>

                            <?php else: ?>

                                Not available

                            <?php endif; ?>

                        </div>



                        <div class="meta-item">

                            <strong>Application Deadline:</strong>



                            <?php if ($deadlineTimestamp !== false): ?>

                                <?= date(

                                    'd M Y, h:i A',

                                    $deadlineTimestamp

                                ) ?>

                            <?php else: ?>

                                Not set

                            <?php endif; ?>

                        </div>



                    </div>



                    <div class="job-footer">



                        <?php if ($isClosed): ?>



                            <p class="closed">

                                Applications for this job are closed.

                            </p>



                            <button type="button" class="btn" disabled>

                                Applications Closed

                            </button>



                        <?php elseif (isset($_SESSION['user_id'])): ?>



                            <?php if ($isJobSeeker): ?>



                                <p class="open">

                                    Applications are open.

                                </p>



                                <form action="apply.php" method="POST">



                                    <input

                                        type="hidden"

                                        name="job_id"

                                        value="<?= (int) $job['id'] ?>"

                                    >



                                    <button

                                        type="submit"

                                        class="btn btn-primary"

                                    >

                                        Apply Now &rarr;

                                    </button>



                                </form>



                            <?php elseif ($isAdmin): ?>



                                <p>

                                    You are viewing this job as an admin.

                                </p>



                            <?php else: ?>



                                <p>

                                    Your account cannot apply for jobs.

                                </p>



                            <?php endif; ?>



                        <?php else: ?>



                            <p>Interested in this opportunity?</p>



                            <a href="login.php" class="btn btn-primary">

                                Login to Apply &rarr;

                            </a>



                        <?php endif; ?>



                    </div>



                </article>



            <?php endforeach; ?>



        <?php endif; ?>



    </section>



</main>



<footer>

    <p>&copy; <?= date('Y') ?> Job Portal. All rights reserved.</p>

</footer>



</body>

</html>
