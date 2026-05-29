import {
    Alignment,
    Autoformat,
    BlockQuote,
    Bold,
    ButtonView,
    ClassicEditor,
    Code,
    CodeBlock,
    Essentials,
    FontBackgroundColor,
    FontColor,
    Fullscreen,
    Heading,
    Highlight,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    Plugin,
    RemoveFormat,
    SourceEditing,
    Strikethrough,
    Subscript,
    Superscript,
    Table,
    TableToolbar,
    Underline,
} from "ckeditor5";

const editors = {};

class AestheticCartUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(
            (file) =>
                new Promise((resolve, reject) => {
                    const formData = new FormData();

                    formData.append("file", file);

                    axios
                        .post("media", formData)
                        .then((response) => {
                            resolve({ default: response.data.path });
                        })
                        .catch((error) => {
                            reject(
                                error.response?.data?.message ||
                                    "Upload failed."
                            );
                        });
                })
        );
    }

    abort() {}
}

function uploadAdapterPlugin(editor) {
    editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new AestheticCartUploadAdapter(loader);
    };
}

const mediaGalleryIcon = `<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
    <path d="M2 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4zm2 0v8.586l2.293-2.293a1 1 0 0 1 1.414 0L12 14.586l2.293-2.293a1 1 0 0 1 1.414 0L16 14.586V4H4zm10 10H6.414l2-2L12 13.586l2-2L14 11.586V14zM7 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" fill="currentColor"/>
</svg>`;

function mediaGalleryLabel() {
    return (
        window.AestheticCart?.langs?.["admin::admin.buttons.media_gallery"] ||
        "Media gallery"
    );
}

function replaceImageLabel() {
    return (
        window.AestheticCart?.langs?.["admin::admin.buttons.replace_image"] ||
        "Replace from gallery"
    );
}

function openMediaGallery(callback) {
    if (typeof window.MediaPicker === "undefined") {
        window.notify?.(
            "error",
            "Media library is not available. Please reload the page."
        );

        return;
    }

    const picker = new window.MediaPicker({ type: "image", multiple: false });

    picker.on("select", ({ path }) => {
        if (path) {
            callback(path);
        }
    });
}

function insertOrReplaceImage(editor, url) {
    const selectedElement = editor.model.document.selection.getSelectedElement();

    if (
        selectedElement &&
        (selectedElement.is("element", "imageBlock") ||
            selectedElement.is("element", "imageInline"))
    ) {
        editor.model.change((writer) => {
            writer.setAttribute("src", url, selectedElement);
            writer.removeAttribute("width", selectedElement);
            writer.removeAttribute("height", selectedElement);
        });

        return;
    }

    editor.execute("insertImage", { source: url });
}

class MediaGalleryPlugin extends Plugin {
    static get pluginName() {
        return "MediaGallery";
    }

    init() {
        const editor = this.editor;

        editor.ui.componentFactory.add("mediaGallery", () => {
            const button = new ButtonView();
            const selectedElement =
                editor.model.document.selection.getSelectedElement();
            const isImageSelected =
                selectedElement &&
                (selectedElement.is("element", "imageBlock") ||
                    selectedElement.is("element", "imageInline"));

            button.set({
                label: isImageSelected ? replaceImageLabel() : mediaGalleryLabel(),
                icon: mediaGalleryIcon,
                tooltip: true,
            });

            button.on("execute", () => {
                openMediaGallery((path) => {
                    insertOrReplaceImage(editor, path);
                });
            });

            return button;
        });
    }
}

function wrapEditor(ckeditorInstance) {
    return {
        on(event, callback) {
            if (event === "change") {
                ckeditorInstance.model.document.on("change:data", () => {
                    ckeditorInstance.updateSourceElement();
                    callback();
                });
            }
        },
        save() {
            ckeditorInstance.updateSourceElement();
        },
        getElement() {
            return ckeditorInstance.sourceElement;
        },
        focus() {
            ckeditorInstance.editing.view.focus();
        },
        setContent(content) {
            ckeditorInstance.setData(content);
            ckeditorInstance.updateSourceElement();
        },
        execCommand() {
            // TinyMCE compatibility no-op (e.g. mceCancel on form reset).
        },
        hasSourceEditing() {
            return ckeditorInstance.plugins.has("SourceEditing");
        },
        isSourceMode() {
            if (!this.hasSourceEditing()) {
                return false;
            }

            return Boolean(
                ckeditorInstance.plugins.get("SourceEditing")
                    .isSourceEditingMode
            );
        },
        toggleSource() {
            if (!this.hasSourceEditing()) {
                return;
            }

            const sourceEditing =
                ckeditorInstance.plugins.get("SourceEditing");

            sourceEditing.isSourceEditingMode =
                !sourceEditing.isSourceEditingMode;
        },
        getCkeditor() {
            return ckeditorInstance;
        },
    };
}

function pageEditorConfig() {
    return {
        heading: {
            options: [
                {
                    model: "paragraph",
                    title: "Paragraph",
                    class: "ck-heading_paragraph",
                },
                {
                    model: "heading2",
                    view: "h2",
                    title: "Heading 2",
                    class: "ck-heading_heading2",
                },
                {
                    model: "heading3",
                    view: "h3",
                    title: "Heading 3",
                    class: "ck-heading_heading3",
                },
                {
                    model: "heading4",
                    view: "h4",
                    title: "Heading 4",
                    class: "ck-heading_heading4",
                },
            ],
        },
    };
}

function buildConfig(editorOptions = {}) {
    return {
        licenseKey: "GPL",
        plugins: [
            Essentials,
            Autoformat,
            PasteFromOffice,
            Bold,
            Italic,
            Underline,
            Strikethrough,
            Code,
            Subscript,
            Superscript,
            BlockQuote,
            Heading,
            Link,
            List,
            Paragraph,
            Alignment,
            Indent,
            IndentBlock,
            FontColor,
            FontBackgroundColor,
            RemoveFormat,
            Highlight,
            HorizontalLine,
            CodeBlock,
            Image,
            ImageCaption,
            ImageResize,
            ImageStyle,
            ImageToolbar,
            ImageUpload,
            uploadAdapterPlugin,
            MediaGalleryPlugin,
            Table,
            TableToolbar,
            MediaEmbed,
            Fullscreen,
            SourceEditing,
        ],
        toolbar: {
            items: [
                "undo",
                "redo",
                "|",
                "heading",
                "|",
                "bold",
                "italic",
                "underline",
                "strikethrough",
                "code",
                "subscript",
                "superscript",
                "|",
                "fontColor",
                "fontBackgroundColor",
                "highlight",
                "removeFormat",
                "|",
                "alignment",
                "-",
                "bulletedList",
                "numberedList",
                "outdent",
                "indent",
                "|",
                "link",
                "uploadImage",
                "mediaGallery",
                "insertTable",
                "mediaEmbed",
                "blockQuote",
                "horizontalLine",
                "codeBlock",
                "|",
                "fullscreen",
                "sourceEditing",
            ],
            shouldNotGroupWhenFull: true,
        },
        heading: {
            options: [
                {
                    model: "paragraph",
                    title: "Paragraph",
                    class: "ck-heading_paragraph",
                },
                {
                    model: "heading1",
                    view: "h1",
                    title: "Heading 1",
                    class: "ck-heading_heading1",
                },
                {
                    model: "heading2",
                    view: "h2",
                    title: "Heading 2",
                    class: "ck-heading_heading2",
                },
                {
                    model: "heading3",
                    view: "h3",
                    title: "Heading 3",
                    class: "ck-heading_heading3",
                },
                {
                    model: "heading4",
                    view: "h4",
                    title: "Heading 4",
                    class: "ck-heading_heading4",
                },
            ],
        },
        highlight: {
            options: [
                {
                    model: "yellowMarker",
                    class: "marker-yellow",
                    title: "Yellow marker",
                    color: "var(--ck-highlight-marker-yellow)",
                    type: "marker",
                },
                {
                    model: "greenMarker",
                    class: "marker-green",
                    title: "Green marker",
                    color: "var(--ck-highlight-marker-green)",
                    type: "marker",
                },
                {
                    model: "pinkMarker",
                    class: "marker-pink",
                    title: "Pink marker",
                    color: "var(--ck-highlight-marker-pink)",
                    type: "marker",
                },
            ],
        },
        image: {
            toolbar: [
                "mediaGallery",
                "|",
                "imageStyle:inline",
                "imageStyle:block",
                "imageStyle:side",
                "|",
                "toggleImageCaption",
                "imageTextAlternative",
                "|",
                "resizeImage",
            ],
        },
        table: {
            contentToolbar: ["tableColumn", "tableRow", "mergeTableCells"],
        },
        language: AestheticCart.rtl ? "ar" : undefined,
        ...editorOptions,
    };
}

async function initElement(element, options = {}) {
    if (element.dataset.ckeditorInit === "true") {
        return;
    }

    element.dataset.ckeditorInit = "true";

    const { setup, ...editorOptions } = options;
    const isPageEditor = element.classList.contains("page-content-editor");
    const config = buildConfig(
        isPageEditor
            ? { ...pageEditorConfig(), ...editorOptions }
            : editorOptions
    );

    const editor = await ClassicEditor.create(element, config);

    const key = element.getAttribute("name") || element.id;

    if (key) {
        editors[key] = wrapEditor(editor);
    }

    if (setup) {
        setup(editors[key]);
    }
}

export default function initWysiwyg(options = {}) {
    document
        .querySelectorAll("textarea.wysiwyg")
        .forEach((element) => initElement(element, options));

    return {
        get(id) {
            return editors[id];
        },
    };
}
