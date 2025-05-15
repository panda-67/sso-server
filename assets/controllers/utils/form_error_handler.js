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

      const name = this.root.name;
      const input = this.root.querySelector(`[id="${name}_${field}"]`);
      const parentDiv = input.parentElement;
      if (!input) continue;

      const errorEl = document.createElement("div");
      errorEl.className = "form-error text-xs text-red-600 mt-1";
      errorEl.textContent = messages.join(", ");

      if (field === "agreeTerms") {
        errorEl.classList.add("w-full", "flex", "justify-center", "mb-2");
        parentDiv.insertAdjacentElement("afterend", errorEl);
      } else {
        input.insertAdjacentElement("afterend", errorEl);
      }
    }
  }

  _showGlobal(messages) {
    Swal.fire({
      icon: "warning",
      title: " Failed",
      html: messages.join("<br>"),
    });
  }
}
