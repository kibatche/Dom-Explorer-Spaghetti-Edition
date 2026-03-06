export interface LexborNode {
     nodeType: number | 0;
     nodeName: string;
     nodeValue: string | null;
     textContent: string;
     childNodes: LexborNode[];
}

export interface LexborText extends LexborNode {
    wholeText: string;
    data: string;
    length: Number | null;
}

export interface LexborCDATASection extends LexborText {}

export interface LexborComment extends LexborNode {}

export interface LexborElement extends LexborNode {
    namespaceURI: string | null;
    localName: string | null;
    tagName?: string;
    hasAttributes: boolean | false;
    attributes: { name: string; value: string }[];
    innerHTML: string | null;
    isTemplate: boolean;
}

export interface LexborDocumentType extends LexborNode {
    name: string | '';
    publicId: string | '';
    systemId: string | '';
}

export interface LexborHtmlDocument extends LexborNode {
    URL: string | null;
    documentURI: string | null;
    characterSet: string | null;
    charset: string | null;
    inputEncoding: string | null;
    title: string;
}