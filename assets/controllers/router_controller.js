import { Controller } from "@hotwired/stimulus";
import axios from "axios";

export default class extends Controller {
  static targets = ["element"];

  connect() {
    // On first load, manually trigger GET /auth/login
    if (window.location.pathname === "/") {
      this.load("/auth/login");
    } else {
      this.load(window.location.pathname, false);
    }
  }

  async load(url, push = true) {
    try {
      const response = await axios.get(url);

      if (response.data?.html) {
        this.elementTarget.innerHTML = response.data.html;

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
    console.log("Pop:", target);
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
    const url = form.action;
    const method = form.method.toUpperCase();
    const formData = new FormData(form);

    try {
      const response = await axios({
        method,
        url,
        data: formData,
      });

      const data = response.data;

      if (data.redirect) {
        this.load(data.redirect);
      } else if (data.html) {
        this.element.innerHTML = data.html;
      }
    } catch (error) {
      console.error("Form submission failed", error);
    }
  }
}
