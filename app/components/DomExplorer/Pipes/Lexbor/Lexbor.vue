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
        <p>Select the version to use for DOMPurify.</p>
        <SearchInput
          v-model="pipe.opts.version"
          :read-only="readOnly"
          label="Version"
          :choices="versions"
        />
      </PipeOption>
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
import type { LexborHtmlDocument, LexborNode } from "./Lexbor.type.js"
import RenderLexborNode from "../../Render/RenderLexborNode.vue";

const versions = ["2.7.0", "php-8.4.18-dom"];

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
          "lexborVersion": opt.version
        }),
      }
    );
    const res = await f.text()
    // console.log(res)
    return res
  },
  () => {},
);

const node = ref<LexborNode>();
const result = ref("");

selectorError.value = "";
parserError.value = "";

watchEffect(() => {
  fetchLexborHtmlDocument(toRaw(props.pipe.opts), `<!DOCTYPE html>${props.input}`)
  .then((jsonStr) => {
    let doc = JSON.parse(jsonStr) as LexborHtmlDocument

    // childNodes[1] = <html>, its childNodes[0] = <head>, childNodes[1] = <body>
    node.value = doc.childNodes[1]?.childNodes[1];

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
