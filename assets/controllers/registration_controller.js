import { Controller } from "@hotwired/stimulus";
import axios from "axios";
import FormErrorHandler from "./utils/form_error_handler.js";

export default class extends Controller {
  static targets = ["form"];

  initialize() {
    this.errorHandler = new FormErrorHandler(this.element);
  }

  connect() {
    this.form = this.element;
  }

  async submit(event) {
    event.preventDefault();

    const url = this.form.action;
    const formData = new FormData(this.form);

    try {
      const response = await axios.post(url, formData);
      const data = response.data;

      // Store token (adapt to your storage method)
      localStorage.setItem("jwt", data.token);

      // Redirect
      axios.get(data.redirect_to, {
        headers: {
          Authorization: `Bearer ${data.token}`,
        },
      });
    } catch (error) {
      const errors = error.response?.data?.errors;

      if (errors) {
        this.errorHandler.show(errors);
      } else {
        this.errorHandler._showGlobal(["Unexpected error. Please try again."]);
      }
    }
  }
}
