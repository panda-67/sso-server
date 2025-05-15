# ⚡ Symfony + Stimulus SPA Architecture

This project implements a **lightweight Single Page Application (SPA)** using:

- Symfony (backend, routing, templating)
- Twig (for both full and partial HTML)
- Stimulus (for SPA routing and interactions)
- Axios (for HTTP requests)

> ✅ No Inertia, no full-blown frontend framework. Just HTML, Stimulus, and clean server-side rendering.

---

## 🧠 SPA Philosophy

This setup avoids a traditional frontend-heavy SPA by:

- Keeping **HTML rendering server-side** with Twig
- Sending **partial HTML fragments via JSON** when navigation happens
- Using a **Stimulus controller** to inject content and manage history
- Maintaining full page layout on hard reloads

---

## 📁 Folder Structure

---

## 🛠️ Stimulus Router Controller

Path: `assets/controllers/router_controller.js`

```js
import { Controller } from "@hotwired/stimulus";
import axios from "axios";

export default class extends Controller {
  static targets = ["navbar", "element"];

  initialize() {
    this.errorHandler = new FormErrorHandler(this.elementTarget);
    this.jwt = localStorage.getItem("jwt");
  }

  connect() {
    if (window.location.pathname === "/") {
      const redirect = this.jwt ? "/api/profile" : "/auth/login";
      this.load(redirect);
    } else {
      this.load(window.location.pathname, false);
    }
  }

  async load(url, push = true) {
    try {
      const { data } = await axios.get(url);

      if (data?.html) {
        this.elementTarget.innerHTML = data.html;

        if (push) {
          history.pushState(null, "", url);
        }
      }
    } catch (error) {
      console.error("Failed to load", url, error);
    }
  }

  popstate() {
    const target = window.location.pathname;
    this.load(target, false);
  }

  navigate(event) {
    event.preventDefault();
    const url = event.currentTarget.href;
    this.load(url);
  }

  async submit(event) {
    event.preventDefault();
    const form = event.target;
    const payload = serializeFormToJson(form);

    try {
      const { data } = await axios({
        method: form.method,
        url: form.action,
        data: payload,
      });

      if (data.token) {
        localStorage.setItem("jwt", data.token);
      }

      if (data.redirect_to) {
        this.load(data.redirect_to);
        if (data.navbar) {
          this.navbarTarget.innerHTML = data.navbar;
        }
      } else if (data.html) {
        this.elementTarget.innerHTML = data.html;
      } else {
        console.warn("Unexpected response format", data);
      }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        // Login failed — show error
        const message = error.response.data?.message || "Login failed";
        this.errorHandler._showGlobal([message]);
      } else {
        console.error("Form submission failed", error);
      }
    }
  }

  async logout(event) {
    event.preventDefault();
    const url = event.currentTarget.href;

    try {
      await axios.post(url);
      localStorage.removeItem("jwt");
      window.location.href = "/";
    } catch (error) {
      console.error("Logout failed:", error);
      // Optionally show a message to the user
    }
  }
}
```

## app.html.twig

```twig
<main
  id="app"
  data-controller="router"
  data-router-target="element"
  data-action="popstate@window->router#popstate"
  class="w-full max-w-4xl bg-white p-6 rounded shadow"
>
  {{ content|default('')|raw }}
</main>
```

1. This is the only element Stimulus swaps.

2. On initial load: {{ content }} holds the server-rendered partial.

3. After first load: content is fetched via axios and inserted via this.elementTarget.innerHTML.

### How Controllers Return Content

```php
public function login(Request $request): Response
{
    $html = $this->renderView('partials/login.partial.html.twig', [
        'loginForm' => $form->createView(),
    ]);

    if ($request->isXmlHttpRequest()) {
        return new JsonResponse(['html' => $html]);
    }

    return $this->render('app.html.twig', ['content' => $html]);
}

```

Logic:

1. AJAX request → `Return { html: "...partial html..." }`

2. Direct browser request → Return full layout with content inserted

### Preventing Reload/JSON View Issues

If a user hard-reloads a pushed route (/auth/login), Symfony returns the full page with the layout.

Ensure:

1.  Your controller never sends JSON for non-AJAX requests

2.  Use `isXmlHttpRequest()` as shown above
