import {
  ClassicEditor,
  Autosave,
  Essentials,
  Paragraph,
  LinkImage,
  Link,
  ImageBlock,
  ImageToolbar,
  BlockQuote,
  Bold,
  CloudServices,
  ImageUpload,
  ImageInsertViaUrl,
  AutoImage,
  Table,
  TableToolbar,
  Mention,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  Heading,
  Highlight,
  HorizontalLine,
  ImageTextAlternative,
  ImageCaption,
  ImageStyle,
  Indent,
  IndentBlock,
  ImageInline,
  Italic,
  AutoLink,
  List,
  ImageUtils,
  ImageEditing,
  Strikethrough,
  TableCaption,
  Alignment,
  TodoList,
  Underline,
  MediaEmbed,
  SimpleUploadAdapter,
} from "./ckeditor5/index.js";

import translations from "./ckeditor5/translations/ko.js";

const LICENSE_KEY = "GPL";

const editorConfig = {
  toolbar: {
    items: [
      "undo",
      "redo",
      "|",
      "heading",
      "|",
      "fontSize",
      "fontFamily",
      "fontColor",
      "fontBackgroundColor",
      "|",
      "bold",
      "italic",
      "underline",
      "|",
      "link",
      "insertTable",
      "highlight",
      "blockQuote",
      "|",
      "alignment",
      "|",
      "bulletedList",
      "numberedList",
      "todoList",
      "outdent",
      "indent",
    ],
    shouldNotGroupWhenFull: false,
  },
  plugins: [
    Alignment,
    AutoImage,
    AutoLink,
    Autosave,
    BlockQuote,
    Bold,
    CloudServices,
    Essentials,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    Heading,
    Highlight,
    HorizontalLine,
    ImageBlock,
    ImageCaption,
    ImageEditing,
    ImageInline,
    ImageInsertViaUrl,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    ImageUtils,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    MediaEmbed,
    Mention,
    Paragraph,
    Strikethrough,
    Table,
    TableCaption,
    TableToolbar,
    TodoList,
    Underline,
    SimpleUploadAdapter,
  ],
  fontFamily: {
    supportAllValues: true,
  },
  fontSize: {
    options: [10, 12, 14, "default", 18, 20, 22],
    supportAllValues: true,
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
      {
        model: "heading5",
        view: "h5",
        title: "Heading 5",
        class: "ck-heading_heading5",
      },
      {
        model: "heading6",
        view: "h6",
        title: "Heading 6",
        class: "ck-heading_heading6",
      },
    ],
  },
  image: {
    toolbar: ["toggleImageCaption", "imageTextAlternative", "|", "imageStyle:inline", "imageStyle:wrapText", "imageStyle:breakText"],
  },
  language: "ko",
  licenseKey: LICENSE_KEY,
  link: {
    addTargetToExternalLinks: true,
    defaultProtocol: "https://",
    decorators: {
      toggleDownloadable: {
        mode: "manual",
        label: "Downloadable",
        attributes: {
          download: "file",
        },
      },
    },
  },
  mention: {
    feeds: [
      {
        marker: "@",
        feed: [
          /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
        ],
      },
    ],
  },
  menuBar: {
    isVisible: true,
  },
  placeholder: "내용을 입력하세요",
  table: {
    contentToolbar: ["tableColumn", "tableRow", "mergeTableCells"],
  },
  translations: [translations],
  simpleUpload: {
    // The URL that the images are uploaded to.
    uploadUrl: "/upload_image.php",

    // Enable the XMLHttpRequest.withCredentials property.
    // I will use php server side logic: is_admin(), if false upload_image.php will return 403 error code.
    withCredentials: false,

    // Headers sent along with the XMLHttpRequest to the upload server.
    // headers: {
    //   "X-CSRF-TOKEN": "CSRF-Token",
    //   Authorization: "Bearer <JSON Web Token>",
    // },
  },
};

const target = document.querySelector("#ck-editor");
const myEditor = null;
if (target) {
  ClassicEditor.create(target, editorConfig)
    .then((editor) => {
      window.myEditor = editor;
    })
    .catch((error) => {
      console.error("CKEditor init failed", error);
    });
}
