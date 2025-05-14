export function serializeFormToJson(form) {
    const formData = new FormData(form);
    const json = {};

    for (const [key, value] of formData.entries()) {
        const input = form.querySelector(`[name="${key}"]`);

        if (input?.dataset?.json !== undefined) {
            const jsonKey = input.dataset.json || key;
            json[jsonKey] = value;
        }
    }

    return json;
}
