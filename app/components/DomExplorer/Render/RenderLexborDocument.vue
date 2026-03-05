<template>
  <template v-for="(node, idx) of childNodes" :key="idx">
    <RenderLexborNode :node="node" />
  </template>
</template>

<script lang="ts" setup>
import type { LexborHtmlDocument } from '../Pipes/Lexbor/Lexbor.type';
import RenderLexborNode from './RenderLexborNode.vue';

const props = defineProps<{
  fragment: LexborHtmlDocument;
}>();

const childNodes = computed(() => {
  if (props.fragment.nodeType === Node.DOCUMENT_NODE) {
    return Array.from(props.fragment.childNodes);
  }

  return [];
});
</script>
