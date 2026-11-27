import { EditorView, basicSetup } from "codemirror";

import { html } from "@codemirror/lang-html";
import { css } from "@codemirror/lang-css";
import { javascript } from "@codemirror/lang-javascript";
import { php } from "@codemirror/lang-php";
import { python } from "@codemirror/lang-python";
import { json } from "@codemirror/lang-json";
import { xml } from "@codemirror/lang-xml";
import { markdown } from "@codemirror/lang-markdown";
import { yaml } from "@codemirror/lang-yaml";
import { sql } from "@codemirror/lang-sql";

import { oneDark } from "@codemirror/theme-one-dark";
import { EditorView as View } from "@codemirror/view";

const getLanguage = (extension) => {
    switch (extension.toLowerCase()) {
        case "html":
        case "htm":
            return html();

        case "css":
        case "scss":
        case "sass":
        case "less":
            return css();

        case "js":
        case "mjs":
        case "cjs":
            return javascript();

        case "ts":
            return javascript({ typescript: true });

        case "tsx":
            return javascript({ typescript: true, jsx: true });

        case "jsx":
            return javascript({ jsx: true });

        case "vue":
            return html();

        case "php":
        case "phtml":
            return php();

        case "py":
            return python();

        case "json":
            return json();

        case "xml":
        case "svg":
            return xml();

        case "md":
            return markdown();

        case "yml":
        case "yaml":
            return yaml();

        case "sql":
            return sql();

        default:
            return [];
    }
};

window.projectCodeEditor = (wire, initialContent, extension) => ({
    view: null,

    init() {
        const darkMode = document.documentElement.classList.contains("dark");

        this.view = new EditorView({
            doc: initialContent,

            extensions: [
                basicSetup,

                getLanguage(extension),

                ...(darkMode ? [oneDark] : []),

                View.theme({
                    "&": {
                        height: "100%",
                        fontSize: "14px",
                    },

                    ".cm-scroller": {
                        overflow: "auto",
                        fontFamily:
                            "ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace",
                    },

                    ".cm-content": {
                        padding: "16px 0",
                        minHeight: "100%",
                    },

                    ".cm-line": {
                        padding: "0 16px",
                    },

                    ".cm-gutters": {
                        borderRight: darkMode
                            ? "1px solid #374151"
                            : "1px solid #e5e7eb",
                        backgroundColor: darkMode ? "#1f2937" : "#f9fafb",
                        color: darkMode ? "#9ca3af" : "#9ca3af",
                    },

                    ".cm-activeLine": {
                        backgroundColor: darkMode ? "#ffffff0a" : "#00000008",
                    },

                    ".cm-activeLineGutter": {
                        backgroundColor: darkMode ? "#ffffff0a" : "#00000008",
                    },

                    "&.cm-focused": {
                        outline: "none",
                    },
                }),

                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        const content = update.state.doc.toString();

                        // Deferred update: tidak mengirim request
                        // Livewire setiap kali user mengetik.
                        wire.set("content", content, false);
                    }
                }),
            ],

            parent: this.$refs.editor,
        });

        this.view.focus();
    },

    destroy() {
        if (this.view) {
            this.view.destroy();
            this.view = null;
        }
    },
});
