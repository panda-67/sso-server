import Swal from "sweetalert2";

export default class FormErrorHandler {
  constructor(rootElement) {
    this.root = rootElement;
  }

  clear() {
    this.root.querySelectorAll(".form-error").forEach((el) => el.remove());
  }

  show(errors) {
    this.clear();

    for (const [field, messages] of Object.entries(errors)) {
      if (field === "_global") {
        this._showGlobal(messages);
        continue;
      }

      const formName = this.root.name;
      const input = this.root.querySelector(`[id="${formName}_${field}"]`);
      if (!input) continue;

      const errorEl = document.createElement("div");
      errorEl.className = "form-error text-xs text-red-600 mt-1";
      errorEl.textContent = messages.join(", ");
      input.insertAdjacentElement("afterend", errorEl);
    }
  }

  _showGlobal(messages) {
    const html = document.createElement("p");
    html.className = "text-sm text-red-600 mt-1";
    html.innerHTML = messages.join("<br>");

    Swal.fire({
      icon: "warning",
      title: "Form Submission Failed",
      html: html,
    });
  }
}
