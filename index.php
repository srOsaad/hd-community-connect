<!DOCTYPE html>
<html lang="en-AU">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Community Connect — Home</title>
  <meta name="description" content="Community Connect is a not-for-profit supporting community programs, volunteer registration, events, donations and access to support resources.">

  <!-- Google Fonts — credited in the footer -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&amp;family=Source+Serif+4:wght@600;700&amp;display=swap">
  
  <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/styles.css">

  <style>
    /* Embedded CSS */

    .hero {
      position: relative;
      padding: clamp(1.75rem, 5vw, 3rem);
      background-image: linear-gradient(135deg, var(--surface) 0%, var(--brand-light) 100%);
    }

    .hero .eyebrow {
      margin: 0 0 0.4em;
      color: var(--ink-muted);
      font-size: var(--step--1);
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    /* Just to make it more visible */
    .focus-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3rem;
      height: 3rem;
      margin-bottom: 0.75rem;
      border-radius: 50%;
      background-color: var(--brand-light);
      color: var(--brand);
      font-family: var(--font-heading);
      font-size: var(--step-1);
      font-weight: 700;
    }

    /* Acknowledgement of Country and the Community quote: plain text, no
       panel or accent border. Also let their paragraphs use the full
       column width instead of the 65ch cap on main p/li (and, for the
       blockquote, its own default side margins), so neither wraps more
       than it needs to. */
    .acknowledgement p,
    blockquote p {
      max-width: none;
    }

    blockquote {
      margin: 0 0 0.75rem;
    }

    .table-wrap {
      overflow-x: auto;
      margin-bottom: 1rem;
    }

    ul.grid {
      list-style: none;
      padding: 0;
    }
  </style>

</head>

<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <!-- Header and nav -->
  <header class="site-header">
    <a class="brand" href="index.html">
      <img class="logo" src="assets/logo.png"
           alt="Community Connect logo" width="48" height="48">
      <span class="brand-name">Community Connect</span>
    </a>

    <nav class="site-nav" aria-label="Main navigation">
      <details>
        <summary aria-label="Menu"></summary>
      </details>
      <ul>
        <li><a href="index.html" aria-current="page">Home</a></li>
        <li><a href="jobs.html">Jobs</a></li>
        <li><a href="apply.html">Apply</a></li>
        <li><a href="about.html">About</a></li>
      </ul>
    </nav>
  </header>

  <main id="main">

    <section class="hero box">
      <p class="eyebrow" style="font-style: italic;">Not-for-profit organisation</p>
      <h1>Community Connect</h1>
      <p class="intro">Bringing people and support closer together.</p>
      <p>Community Connect is a not-for-profit organisation improving its digital
        services to support community programs, volunteer registration, event
        information, donations and access to support resources.</p>
      <div class="group">
        <a class="btn" href="jobs.html">View job openings</a>
        <a class="btn-secondary" href="about.html">Learn about us</a>
      </div>
    </section>

    <section aria-labelledby="about-community-heading">
      <h2 id="about-community-heading">What Is a Community?</h2>
      <blockquote cite="https://en.wikipedia.org/wiki/Community">
        <p>A community is a social unit (a group of people) with one or more
          shared socially-significant characteristics, being place, set of
          norms, culture, religion, values, customs, or identity. Communities
          may share a sense of place situated in a given geographical area
          (e.g. a country, village, town, or neighborhood) or in virtual
          space through communication platforms. Durable good relations that
          extend beyond immediate genealogical ties also define a sense of
          community, important to people's identity, practice, and roles in
          social institutions such as family, home, work, government,
          society, or humanity at large.</p>
      </blockquote>
      <p class="hint">Source: <a href="https://en.wikipedia.org/wiki/Community"
           target="_blank" rel="noopener noreferrer">"Community"<span
           class="visually-hidden"> (opens in a new tab)</span></a>, Wikipedia,
        licensed under <a href="https://creativecommons.org/licenses/by-sa/4.0/"
           target="_blank" rel="noopener noreferrer">CC BY-SA 4.0<span
           class="visually-hidden"> (opens in a new tab)</span></a>.</p>
    </section>

    <section aria-labelledby="focus-heading">
      <h2 id="focus-heading">What we help with</h2>
      <ul class="grid">
        <li class="box">
          <span class="focus-icon" aria-hidden="true">C</span>
          <h3>Community Programs</h3>
          <p>Coordinating local initiatives that connect people with the
            services and support their community offers.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">V</span>
          <h3>Volunteer Registration</h3>
          <p>A simple sign-up process so volunteers can find opportunities
            that match their time and skills.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">E</span>
          <h3>Event Information</h3>
          <p>Up-to-date details on community events, so nobody misses a
            chance to get involved.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">D</span>
          <h3>Donations &amp; Resources</h3>
          <p>Secure ways to give, alongside a directory of support
            resources for people who need them.</p>
        </li>
      </ul>
    </section>

    <section aria-labelledby="programs-heading">
      <h2 id="programs-heading">Support Programs at a Glance</h2>
      <div class="table-wrap">
        <table>
          <caption>Overview of Community Connect's core programs</caption>
          <thead>
            <tr>
              <th scope="col">Program</th>
              <th scope="col">Focus</th>
              <th scope="col">Delivery</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row" rowspan="2">Community Programs</th>
              <td>Volunteer registration</td>
              <td>Online &amp; in-person</td>
            </tr>
            <tr>
              <td>Event coordination</td>
              <td>Online &amp; in-person</td>
            </tr>
            <tr>
              <th scope="row">Donations</th>
              <td colspan="2">Secure online giving portal</td>
            </tr>
            <tr>
              <th scope="row">Support Resources</th>
              <td colspan="2">Directory of local community services</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="acknowledgement" aria-labelledby="acknowledgement-heading">
      <h2 id="acknowledgement-heading">Acknowledgement of Country</h2>
      <p style="text-align: justify;">Community Connect acknowledges the Traditional Owners of the lands on
        which we work, connect and support our community, and recognises
        their continuing connection to land, waters and culture. We pay our
        respects to Elders past and present, and extend that respect to all
        Aboriginal and Torres Strait Islander peoples engaging with our
        services today.</p>
    </section>

  </main>

  <!-- Footer : added all required things and credits -->
  <footer class="site-footer">
    <h2 class="visually-hidden">Site information</h2>

    <ul class="footer-links">
      <li><a href="https://hd-community-connect.atlassian.net/jira/software/projects/CSW/boards/3"
             target="_blank" rel="noopener noreferrer">Jira project board<span
             class="visually-hidden"> (opens in a new tab)</span></a></li>
      <li><a href="https://github.com/srOsaad/hd-community-connect"
             target="_blank" rel="noopener noreferrer">GitHub repository<span
             class="visually-hidden"> (opens in a new tab)</span></a></li>

      <li><a href="mailto:106406031@student.swin.edu.au">106406031@student.swin.edu.au</a></li>
      <li><a href="mailto:106052926@student.swin.edu.au">106052926@student.swin.edu.au</a></li>
      <li><a href="mailto:106424716@student.swin.edu.au">106424716@student.swin.edu.au</a></li>
      <li><a href="mailto:106514329@student.swin.edu.au">106514329@student.swin.edu.au</a></li>
    </ul>

    <p class="footer-note">
      &copy; 2026 Community Connect. A student project by Group HD for
      Web Technology, Swinburne University of Technology.
      Community Connect is a fictional organisation created for assessment purposes.
    </p>

    <!-- add anything you didn't make yourself to this list -->
    <p class="footer-note">
      Acknowledgements: logo generated with Google Gemini.
      "What Is a Community?" quotation adapted from the Wikipedia article
      <a href="https://en.wikipedia.org/wiki/Community"
         target="_blank" rel="noopener noreferrer">Community<span
         class="visually-hidden"> (opens in a new tab)</span></a>,
      licensed under CC BY-SA 4.0.
      Colour scheme adapted from the Australian Services Union
      <a href="https://www.asu.org.au/ASU_NSW/About/community_sector.aspx"
         target="_blank" rel="noopener noreferrer">Social and
      Community Services<span class="visually-hidden"> (opens in a new tab)</span></a> page. Typefaces Source Serif 4 and Source Sans 3 by
      Adobe, served via Google Fonts under the SIL Open Font License.
    </p>
  </footer>

</body>
</html>