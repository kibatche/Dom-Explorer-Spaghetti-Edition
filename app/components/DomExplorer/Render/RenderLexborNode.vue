<template>
  <RenderLexborElement v-if="isElement(node)" :el="node" :depth="depth" />
  <RenderLexborTextFragment v-else-if="isTextFragment(node)" :content="node" :depth="depth" />
  <RenderLexborComment v-else-if="isComment(node)" :comment="node" :depth="depth" />
  <RenderLexborDocument v-else-if="isDocument(node)" :fragment="node" :depth="depth" />
  <RenderDoctype v-else-if="isDoctype(node)" :doctype="node" :depth="depth" />
</template>

<script lang="ts" setup>
import type { LexborNode, LexborHtmlDocument, LexborElement, LexborComment, LexborText, LexborDocumentType } from "../Pipes/Lexbor/Lexbor.type"
import RenderLexborComment from "./RenderLexborComment.vue";
import RenderLexborDocument from "./RenderLexborDocument.vue";
import RenderLexborElement from "./RenderLexborElement.vue";
import RenderLexborTextFragment from "./RenderLexborTextFragment.vue";

const props = withDefaults(
  defineProps<{
    node: LexborNode;
    depth?: number;
  }>(),
  { depth: 0 },
);

const nodeType = computed(() => {
  return props.node.nodeType;
});

function isElement(node: LexborNode): node is LexborElement {
  return nodeType.value === Node.ELEMENT_NODE;
}

function isTextFragment(node: LexborNode): node is LexborText {
  return (
    nodeType.value === Node.TEXT_NODE ||
    nodeType.value === Node.CDATA_SECTION_NODE
  );
}

function isComment(node: LexborNode): node is LexborComment {
  return nodeType.value === Node.COMMENT_NODE;
}

function isDocument(node: LexborNode): node is LexborHtmlDocument {
  return nodeType.value === Node.DOCUMENT_NODE;
}

function isDoctype(node: LexborNode): node is LexborDocumentType {
  return nodeType.value === Node.DOCUMENT_TYPE_NODE;
}
</script>
