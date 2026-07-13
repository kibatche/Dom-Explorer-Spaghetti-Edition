<template>
  <Pipe :pipe="pipe" :error="error">
    <template #title>
      <span>Symfony / Html Sanitizer</span>
      <span class="ml-2 text-sm text-muted-foreground">{{
        pipe.opts.version
      }}</span>
    </template>
    <template #description>
      Sanitize the input with
      <ExternalLink
        href="https://github.com/symfony/html-sanitizer"
        label="Symfony / Html Sanitizer"
      />.
    </template>
    <!-- <template #options="{ readOnly }">
      <PipeOption label="Version">
        <p>Select the version to use for Symfony / Html Sanitizer.</p>
        <SearchInput
          v-model="pipe.opts.version"
          :read-only="readOnly"
          label="Version"
          :choices="versions"
        />
      </PipeOption> -->
      <!-- <PipeOption label="Options">
        <p>
          The options to pass to Symfony / Html Sanitizer, this code will be passed to
          <code>eval()</code>.
        </p>
        <CodeEditor
          v-model="pipe.opts.options"
          :read-only="readOnly"
          lang="javascript"
        />
        <div
          v-if="error"
          class="mt-2 rounded-md bg-destructive p-2 text-destructive-foreground"
        >
          {{ error }}
        </div>
      </PipeOption>
      <PipeOption label="Hooks">
        <p>
          If you need to add some hooks to Symfony / Html Sanitizer before sanitizing the
          input, you can use this field. Don't forget to call
          <code>removeAllHooks</code> since the function will be called
          everytime the input change.
        </p>
        <CodeEditor
          v-model="pipe.opts.hooks"
          :read-only="readOnly"
          lang="javascript"
        />
        <div
          v-if="error"
          class="mt-2 rounded-md bg-destructive p-2 text-destructive-foreground"
        >
          {{ error }}
        </div>
      </PipeOption> -->
    <!-- </template> -->

    <template #render>
      <RenderHtml :text="result" />
    </template>
  </Pipe>
</template>

<script lang="ts" setup>
import type { Pipe } from "~/types.js";
import { type Opts } from "./SymfonyHtmlSanitizer.pipe.js";

const props = defineProps<{
  input: string;
  pipe: Pipe<Opts>;
}>();

const emit = defineEmits<{
  update: [string];
}>();

const error = ref<string>();
const result = ref<string>("");

const sanitize = useSandbox(
  async (imp, input: string) => {
    const f = await fetch("http://127.0.0.1:5000/sanitize.php",
      {
        'method': 'POST',
        'body': JSON.stringify({
          "html": input
        }),
      }
    );
    const res = await f.json(); 

    return res.html;
  },
  () => {},
);

watchEffect(() => {
  sanitize(props.input)
    .then((res) => {
      result.value = res;
      error.value = "";
    })
    .catch((e) => {
      error.value = `${e}`;
      result.value = "";
    });
});

watchEffect(() => {
  emit("update", result.value);
});
</script>
