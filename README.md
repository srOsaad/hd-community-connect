# What to use where

| Class / attribute | Use for |
| --- | --- |
| `.btn` / `.btn-secondary` | Solid / outlined button, on `<a>` or `<button>`. One class only, never combine. |
| `.group` | Row of buttons/links, wraps on narrow screens. Important for reponsiveness : div|
| `.box` | Content panel. |
| `.grid` | Keep boxes inside. Important for reponsiveness : div|
| `.intro` | Larger lead paragraph. Use it for the first paragraph after title|
| `.field` | Label + input pair. It is only to add margin below (don't use br, we are using it to keep the margin constant) : div|
| `.choice` | Radio/checkbox with its text, on the `<label class="choice"><input type="radio" name="gender" value="male"> Male </label>`. |
| `.hint` | Small muted text under a field. |
| `aria-current="page"` | Put on your own page's nav link (also, you must move it from home) |