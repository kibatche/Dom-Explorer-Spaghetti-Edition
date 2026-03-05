<template>
  <DomBase :tag="tagName" :ns="ns" :depth="depth" :is-template="isTemplate" :shadowrootmode="props.el.attributes?.find(a => a.name === 'shadowrootmode')?.value">
    <template #attrs>
      <div class="flex flex-wrap gap-2 pl-2 empty:hidden">
        <div v-for="attr of attrs" :key="attr.name" class="flex text-nowrap">
          <span class="text-red-300">{{ attr.name }}</span>
          <span class="text-muted-foreground/50">=</span>
          <span class="quoted text-wrap break-all">{{ attr.value }}</span>
        </div>
        <span v-if="repeating">x{{ repeating + 1 }}</span>
      </div>
    </template>
    <template v-for="(child, idx) in children" :key="idx">
      <RenderLexborNode :node="child" :depth="depth + repeating + 1" />
    </template>
  </DomBase>
</template>

<script lang="ts" setup>
import type { LexborNode, LexborElement } from "../Pipes/Lexbor/Lexbor.type"
import RenderLexborNode from "./RenderLexborNode.vue";



const props = defineProps<{
  el: LexborElement;
  depth: number;
}>();

const ns = computed(() => {
  return props.el.namespaceURI ?? undefined;
});

const tagName = computed(() => {
  return props.el.tagName;
});

const attrs = computed(() => {
  const attrs = props.el.attributes;
  return Array.from(attrs).map((attr) => ({
    name: attr.name,
    value: attr.value,
  }));
});

const isTemplate = computed(() => {
  return props.el.isTemplate;
});

function isElement(node?: LexborNode): node is LexborElement {
  return node?.nodeType === Node.ELEMENT_NODE;
}

const repeating = computed(() => {
  if (props.el.hasAttributes) {
    return 0;
  }

  function isSame(el?: LexborNode) {
    if (!isElement(el)) {
      return false;
    }
    if (el.namespaceURI !== props.el.namespaceURI) {
      return false;
    }
    if (el.tagName !== props.el.tagName) {
      return false;
    }
    if (el.hasAttributes) {
      return false;
    }
    return true;
  }

  let repeating = 0;
  let current: LexborNode | undefined = props.el;
  while (
    current &&
    current.childNodes.length === 1 &&
    isSame(current.childNodes[0])
  ) {
    current = current.childNodes[0];
    repeating++;
  }
  return repeating;
});

const children = computed(() => {
  let children: LexborNode[];

  children = Array.from(props.el.childNodes);

  for (let i = 0; i < repeating.value; i++) {
    children = Array.from(children[0]!.childNodes);
  }

  return Array.from(children);
});
</script>

<style scoped>
.quoted {
  &::before,
  &::after {
    @apply text-muted-foreground/50;
    content: '"';
  }

  padding-left: 1ch;
  text-indent: -1ch;
}
</style>
