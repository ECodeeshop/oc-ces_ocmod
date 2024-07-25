import { basicSetup, EditorView } from "codemirror";
import { keymap } from "@codemirror/view";
import { autocompletion } from "@codemirror/autocomplete";
import { xml } from "@codemirror/lang-xml";
import { tags } from "@lezer/highlight";

import { syntaxHighlighting, HighlightStyle } from "@codemirror/language";
import {
  standardKeymap,
  defaultKeymap,
  indentWithTab,
} from "@codemirror/commands";

const myHighlightStyle = HighlightStyle.define([
  { tag: tags.keyword, color: "#fc6" },
  { tag: tags.comment, color: "#f5d", fontStyle: "italic" },
]);

// Our list of completions (can be static, since the editor
/// will do filtering based on context).
const completions = [
  { label: 'property="', type: "constant" },
  { label: 'offset="', type: "constant" },
  { label: 'position="after"', type: "constant" },
  { label: 'position="before"', type: "constant" },
  { label: 'offset="1"', type: "constant" },
  { label: 'path="', type: "constant" },
  { label: 'modification="', type: "constant" },
  { label: 'name="', type: "constant" },
  { label: 'code="', type: "constant" },
  { label: 'version="', type: "constant" },
  { label: 'link="', type: "constant" },
  { label: 'author="', type: "constant" },
  { label: 'operation="', type: "constant" },
  { label: 'search="', type: "constant" },
  { label: 'regex="', type: "constant" },
  { label: 'add="', type: "constant" },
  { label: 'error="', type: "constant" },
  { label: 'error="skip"', type: "constant" },
  { label: "skip", type: "constant" },
  { label: "system/", type: "constant" },
  { label: "admin/view/template/", type: "constant" },
  { label: "system/library", type: "constant" },
  { label: "catalog/controller/", type: "constant" },
  { label: "catalog/model/", type: "constant" },
  { label: "catalog/language/", type: "constant" },
  { label: "catalog/view/template/", type: "constant" },
  { label: '<xml version="1.0" encoding="utf-8"?>', type: "constant" },
  {
    label:
      '\t<file path="">\n\t\t<operation>\n\t\t\t<search>\n\t\t\t\t<![CDATA[]]>\n\t\t\t</search>\n\t\t\t<add position="after">\n\t\t\t\t<![CDATA[]]>\n\t\t\t</add>\n\t\t</operation>\n\t</file>',
    type: "constant",
    info: "after",
  },
  {
    label:
      '\t<file path="">\n\t\t<operation>\n\t\t\t<search>\n\t\t\t\t<![CDATA[]]>\n\t\t\t</search>\n\t\t\t<add position="before">\n\t\t\t\t<![CDATA[]]>\n\t\t\t</add>\n\t\t</operation>\n\t</file>',
    type: "constant",
    info: "before",
  },
  {
    label:
      '\t<file path="">\n\t\t<operation>\n\t\t\t<search>\n\t\t\t\t<![CDATA[]]>\n\t\t\t</search>\n\t\t\t<add position="replace">\n\t\t\t\t<![CDATA[]]>\n\t\t\t</add>\n\t\t</operation>\n\t</file>',
    type: "constant",
    info: "replace",
  },
  {
    label:
      '<?xml version="1.0" encoding="utf-8"?>\n<modification>\n\t<name>default</name>\n\t<code>default</code>\n\t<version>1.0.0</version>\n\t<author>CodeEshop</author>\n\t<link>https://codeeshop.com</link>\n\t<file path="">\n\t\t<operation>\n\t\t\t<search>\n\t\t\t\t<![CDATA[]]>\n\t\t\t</search>\n\t\t\t<add position="after">\n\t\t\t\t<![CDATA[]]>\n\t\t\t</add>\n\t\t</operation>\n\t</file>\n</modification>',
    type: "constant",
    info: "replace",
  },
];

let myTheme = EditorView.theme(
  {
    "&": {
      color: "white",
      backgroundColor: "#034",
    },
    ".cm-content": {
      caretColor: "#0e9",
    },
    "&.cm-focused .cm-cursor": {
      borderLeftColor: "#0e9",
    },
    "&.cm-focused .cm-selectionBackground, ::selection": {
      backgroundColor: "#074",
    },
    ".cm-gutters": {
      backgroundColor: "#045",
      color: "#ddd",
      border: "none",
    },
    ".cm-content, .cm-gutter": { minHeight: "200px" },
  },
  { dark: true }
);

function myCompletions(context) {
  let before = context.matchBefore(/(<)?(\w+)/);
  // If completion wasn't explicitly started and there
  // is no word before the cursor, don't open completions.
  if (!context.explicit && !before) return null;
  return {
    from: before ? before.from : context.pos,
    options: completions,
    validFor: /^\w*$/,
  };
}

function CodeMirrorWrapper(elementId, args) {
  const { updateElementId, initXmlValue, indent_size } = args;

  let view = new EditorView({
    doc: initXmlValue
      ? initXmlValue
      : "// Modification File https://codemirror.net/ \n",
    extensions: [
      myTheme,
      basicSetup,
      xml(),
      syntaxHighlighting(myHighlightStyle),
      autocompletion({ override: [myCompletions] }),
      keymap.of([defaultKeymap, standardKeymap, indentWithTab]),
      EditorView.updateListener.of((v) => {
        if (v.docChanged && updateElementId) {
          setUpdateElement();
        }
      }),
    ],
    parent: document.querySelector(elementId),
  });

  function setUpdateElement() {
    document.querySelector(updateElementId).innerHTML =
      view.state.doc.toString();
  }

  setUpdateElement();

  // Function to return the current editor value
  function getValue() {
    return view.state.doc.toString();
  }

  // Function to return the current editor view
  function getView() {
    return view;
  }

  // Function to return the current editor view
  function setValue(xmlValue) {
    view.dispatch({
      selection: { from: 0, to: view.state.doc.length },
      changes: { from: 0, to: view.state.doc.length, insert: xmlValue },
    });
  }

  // Function to destroy the CodeMirror instance
  function destroy() {
    view.destroy();
  }

  // Return an object with getValue and destroy methods
  return {
    setValue,
    getValue,
    getView,
    destroy,
  };
}

window.CodeMirror = CodeMirrorWrapper;
