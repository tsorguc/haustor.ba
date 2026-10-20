document.addEventListener("DOMContentLoaded", () => {
  const routes = {
    "#/login": "views/login.html",
    "#/register": "views/register.html",
    "#/resident": "views/resident-dashboard.html",
    "#/manager": "views/manager-dashboard.html",
    "#/map": "views/buildings-map.html",
  };

  const appView = document.getElementById("app-view");
  const sidebarEl = document.getElementById("sidebarOffcanvas");

  async function navigate() {
    const hash = window.location.hash || "#/login";
    const viewPath = routes[hash] || routes["#/login"];

    try {
      const response = await fetch(viewPath);
      if (!response.ok) {
        throw new Error(`Neuspješno učitavanje prikaza: ${response.statusText}`);
      }
      const html = await response.text();
      appView.innerHTML = html;
      updateActiveNav(hash);

      // Zatvori mobilni offcanvas meni nakon odabira stavke na ekranima < 992px
      if (window.innerWidth < 992 && sidebarEl && window.bootstrap) {
        const offcanvasInstance =
          window.bootstrap.Offcanvas.getInstance(sidebarEl);
        if (offcanvasInstance) {
          offcanvasInstance.hide();
        }
      }
    } catch (error) {
      appView.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Greška pri učitavanju prikaza</h4>
          <p>Nije moguće učitati šablon sa putanje <code>${viewPath}</code>.</p>
          <hr>
          <p class="mb-0">Provjerite da li projekat pokrećete putem web servera (npr. XAMPP Apache ili VS Code Live Server) kako bi <code>fetch</code> zahtjevi prema lokalnim datotekama radili ispravno.</p>
        </div>
      `;
    }
  }

  function updateActiveNav(activeHash) {
    document.querySelectorAll(".nav-pills .nav-link").forEach((link) => {
      if (link.getAttribute("href") === activeHash) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }

  window.addEventListener("hashchange", navigate);
  navigate();
});
