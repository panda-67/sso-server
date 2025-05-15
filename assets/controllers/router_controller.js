import { Controller } from "@hotwired/stimulus";
import FormErrorHandler from "./utils/form_error_handler.js";
import { serializeFormToJson } from "./utils/form_serialize.js";
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
        document.title = `PMED | ${data.title ?? "Welcome"}`;

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
