/**
 * This configuration was generated using the CKEditor 5 Builder. You can modify it anytime using this link:
 * https://ckeditor.com/ckeditor-5/builder/#installation/NoJgNARCB0Cc0AYKQIwJCgrLA7JlKAHMQGwDMImZALCSbCNRpulYRbIdTg8hANYB7ZAjDAUYUaIkSEAXUiwAZgBMlmEpghygA===
 */

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
} from "ckeditor5";

import translations from "ckeditor5/translations/ko.js";

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
  initialData:
    '<h2>Congratulations on setting up CKEditor 5! 🎉</h2>\n<p>\n\tYou\'ve successfully created a CKEditor 5 project. This powerful text editor\n\twill enhance your application, enabling rich text editing capabilities that\n\tare customizable and easy to use.\n</p>\n<h3>What\'s next?</h3>\n<ol>\n\t<li>\n\t\t<strong>Integrate into your app</strong>: time to bring the editing into\n\t\tyour application. Take the code you created and add to your application.\n\t</li>\n\t<li>\n\t\t<strong>Explore features:</strong> Experiment with different plugins and\n\t\ttoolbar options to discover what works best for your needs.\n\t</li>\n\t<li>\n\t\t<strong>Customize your editor:</strong> Tailor the editor\'s\n\t\tconfiguration to match your application\'s style and requirements. Or\n\t\teven write your plugin!\n\t</li>\n</ol>\n<p>\n\tKeep experimenting, and don\'t hesitate to push the boundaries of what you\n\tcan achieve with CKEditor 5. Your feedback is invaluable to us as we strive\n\tto improve and evolve. Happy editing!\n</p>\n<h3>Helpful resources</h3>\n<ul>\n\t<li>📝 <a href=\"https://portal.ckeditor.com/checkout?plan=free">Trial sign up</a>,</li>\n\t<li>📕 <a href=\"https://ckeditor.com/docs/ckeditor5/latest/installation/index.html">Documentation</a>,</li>\n\t<li>⭐️ <a href="https://github.com/ckeditor/ckeditor5">GitHub</a> (star us if you can!),</li>\n\t<li>🏠 <a href=\"https://ckeditor.com">CKEditor Homepage</a>,</li>\n\t<li>🧑‍💻 <a href="https://ckeditor.com/ckeditor-5/demo/">CKEditor 5 Demos</a>,</li>\n</ul>\n<h3>Need help?</h3>\n<p>\n\tSee this text, but the editor is not starting up? Check the browser\'s\n\tconsole for clues and guidance. It may be related to an incorrect license\n\tkey if you use premium features or another feature-related requirement. If\n\tyou cannot make it work, file a GitHub issue, and we will help as soon as\n\tpossible!\n</p>\n',
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
  placeholder: "Type or paste your content here!",
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

ClassicEditor.create(document.querySelector("#ck-editor"), editorConfig);
