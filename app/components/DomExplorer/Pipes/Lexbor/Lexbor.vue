<template>
  <Pipe :pipe="pipe" :error="error">
      <template #title>
      <span>Lexbor</span>
      <span class="ml-2 text-sm text-muted-foreground">{{
        pipe.opts.version
      }}</span>
    </template>
    <template #description>
      Parse the input with
      <code>
        <ExternalLink
          href="https://github.com/php/php-src/tree/master/ext/lexbor"
          label="Lexbor"
        />
        <span class="ml-2 text-sm text-muted-foreground">{{
          pipe.opts.version
      }}</span>
      </code>
      and display the result as a tree. Lexbor is used under the hood by 
            <code>
        <ExternalLink
          href="https://www.php.net/manual/fr/book.dom.php"
          label="Php Dom"
        />
      </code>
      .
    </template>

    <template #options="{ readOnly }">
      <PipeOption label="Add doctype">
        <p>
          Add the html doctype to the input before parsing, without it the
          parser will be in Quirks mode.
        </p>
        <Label class="flex items-center gap-1.5">
          <Checkbox v-model="pipe.opts.addDoctype" :disabled="readOnly" /> Add
          doctype
        </Label>
      </PipeOption>
      <PipeOption label="Version">
        <p>Select the version to use for lexbor.</p>
        <SearchInput
          v-model="pipe.opts.version"
          :read-only="readOnly"
          label="Version"
          :choices="versions"
        />
      </PipeOption>
            <PipeOption label="Output">
        <p>
          Choose what to output for the next pipe from the selected element.
        </p>
        <div class="flex items-center gap-2">
          <SearchInput
            v-model="pipe.opts.output"
            :read-only="readOnly"
            label="output"
            :choices="outputChoices"
          />

          <span class="text-sm">- {{ outputDescs[pipe.opts.output] }}</span>
        </div>
      </PipeOption>
      <!-- [PAI] BEGIN — fragment mode options -->
      <PipeOption label="Parse mode">
        <p>
          <b>createFromString</b>: full document parse (<code>tree-&gt;fragment == NULL</code>).<br>
          <b>innerHTML</b>: fragment parse (<code>tree-&gt;fragment != NULL</code>) — HTML tags inside foreign content are <em>not</em> popped.
        </p>
        <SearchInput
          v-model="pipe.opts.parseMode"
          :read-only="readOnly"
          label="Parse mode"
          :choices="parseModeChoices"
        />
      </PipeOption>
      <PipeOption v-if="pipe.opts.parseMode === 'innerHTML'" label="Context element">
        <p>
          Element whose <code>innerHTML</code> is set. Determines the parsing namespace:<br>
          <code>body</code> → HTML, <code>svg</code> → SVG foreign content, <code>math</code> → MathML, <code>table</code> → in-table.
        </p>
        <SearchInput
          v-model="pipe.opts.contextTag"
          :read-only="readOnly"
          label="Context tag"
          :choices="contextTagChoices"
        />
      </PipeOption>
      <!-- [PAI] END -->
    </template>

    <template #render>
        <RenderLexborNode v-if="node" :node="node" :depth="startingDepth" />
        <div v-else class="bg-muted px-1 text-muted-foreground">
          Selector returned nothing :&lpar;
        </div>
    </template>
  </Pipe>
</template>

<script lang="ts" setup>
import type { Pipe } from "~/types.js";
import type { Opts } from "./Lexbor.pipe.js";
import type { LexborElement, LexborHtmlDocument, LexborNode } from "./Lexbor.type.js"
import RenderLexborNode from "../../Render/RenderLexborNode.vue";

const versions = ["2.7.0", "php-8.4.18-dom"];
// [PAI] BEGIN — fragment mode option lists
const parseModeChoices = ["createFromString", "innerHTML"];
const contextTagChoices = ["body", "div", "svg", "math", "table", "caption", "td", "template"];
// [PAI] END

const props = defineProps<{
  input: string;
  pipe: Pipe<Opts>;
}>();

const emit = defineEmits<{
  update: [string];
}>();

  const parserError = ref<string>();
  const selectorError = ref<string>();
  const outputError = ref<string>();
  const error = computed(
  () => parserError.value || selectorError.value || outputError.value,
);

const outputChoices = [
  // "source",
  "innerHTML",
  // "outerHTML",
  // "innerText",
  // "textContent",
];

const outputDescs = {
  // source: "The original input",
  innerHTML: "The inner html of the element",
  // outerHTML: "The outer html of the element",
  // innerText: "The inner text of the element",
  // textContent: "The text content of the element",
};


// Sends the HTML to lexborParser.php and returns the serialized JSON tree as a string.
// useSandbox runs the fetch inside an isolated iframe — only strings can cross the iframe boundary,
// hence JSON.stringify on the response object before returning.
const fetchLexborHtmlDocument = useSandbox(
  async (imp, opt: Opts, input: string) => {
    const f = await fetch("http://127.0.0.1:5000/lexborParser.php",
      {
        'method': 'POST',
        'body': JSON.stringify({
          "html": input,
          "lexborVersion": opt.version,
          "parseMode": opt.parseMode ?? "createFromString",
          "contextTag": opt.contextTag ?? "body",
        }),
      }
    );
    const res = await f.text()
    console.log("res: "+res)
    return res
  },
  () => {},
);

const node = ref<LexborNode>();
const result = ref("");

selectorError.value = "";
parserError.value = "";

watchEffect(() => {
  // [PAI] innerHTML mode gets raw input — no DOCTYPE wrapper (fragment context element provides the namespace)
  const htmlInput = props.pipe.opts.parseMode === 'innerHTML' ? props.input : `<!DOCTYPE html>${props.input}`;
  fetchLexborHtmlDocument(toRaw(props.pipe.opts), htmlInput)
  .then((jsonStr) => {
    
    let doc = JSON.parse(jsonStr) as LexborHtmlDocument;
    // me + PAI
    const html = Array.from(doc.childNodes).find(n => n.nodeType === 1 && n.localName.toLowerCase() === 'html') as LexborElement;
    const body = Array.from(html.childNodes).find(n => n.nodeType === 1 && n.localName.toLowerCase() === 'body') as LexborElement;
    console.log("body")
    console.log("Version: "+props.pipe.opts.version)
    console.log(body)
    node.value = body;
    
    const el = body.innerHTML
    
    switch (props.pipe.opts.output) {
      case "innerHTML":
        result.value = el ?? "";
        break;
      default:
        result.value = "";
        break;
    }
  })
  .catch(e => {
    parserError.value = `${e}`;
    node.value = undefined;
    result.value = "";
  })

});

watchEffect(() => {
  emit("update", result.value);
});

</script>
