#include "./lexbor_html_dom_single.h"
#include "./yyjson.h"
#include <stddef.h>
#include <stdio.h>
#include <string.h>
#include <time.h>

static yyjson_mut_val *node_to_json(lxb_dom_node_t *node, yyjson_mut_doc *doc)
{
    yyjson_mut_val *obj = yyjson_mut_obj(doc);
    size_t textContentLen = 0;
    yyjson_mut_val *children = yyjson_mut_arr(doc);
    
    //Node Interface
    // nodeType
    yyjson_mut_obj_add_int(doc, obj, "nodeType", node->type);


    const lxb_char_t *textContent = lxb_dom_node_text_content(node, &textContentLen);    
    yyjson_mut_obj_add_strn(doc, obj, "textContent", (const char*)textContent, textContentLen);
      
    switch (node->type) {
        case LXB_DOM_NODE_TYPE_ELEMENT:
            lxb_dom_element_t *curr_el = lxb_dom_interface_element(node);
            
            size_t localNameLen = 0;
            size_t tagNameLen = 0;
            size_t nodeNameLen = 0;
            
            const lxb_char_t *localName = lxb_dom_element_local_name(curr_el, &localNameLen);
            const lxb_char_t *tagName = lxb_dom_element_tag_name(curr_el, &tagNameLen);
            const lxb_char_t *nodeName = tagName;
            
            lexbor_str_t innerHTML = {0};
            lxb_html_serialize_deep_str(node, &innerHTML);
            

            yyjson_mut_obj_add_strn(doc, obj, "localName", (const char*)localName, localNameLen);
            yyjson_mut_obj_add_strn(doc, obj, "tagName", (const char*)tagName, tagNameLen);
            yyjson_mut_obj_add_strn(doc, obj, "nodeName", (const char*)nodeName, nodeNameLen);
            yyjson_mut_obj_add_strn(doc, obj, "innerHTML", (const char*)innerHTML.data, innerHTML.length);
            
            switch (node->ns) {
                case LXB_NS_HTML:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/1999/xhtml");
                    break;
                case LXB_NS_MATH:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/1998/Math/MathML");
                    break;
                case LXB_NS_SVG:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/2000/svg");
                    break;
                case LXB_NS_XLINK:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/1999/xlink");
                    break;
                case LXB_NS_XML:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/XML/1998/namespace");
                    break;
                case LXB_NS_XMLNS:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "http://www.w3.org/2000/xmlns/");
                    break;
                default:
                    yyjson_mut_obj_add_str(doc, obj, "namespaceURI", "unknown namespaceURI");
                    break;
            }
            yyjson_mut_val *attributes = yyjson_mut_arr(doc);
            lxb_dom_attr_t *attr = lxb_dom_element_first_attribute(curr_el);
            
            while (attr)
            {
                size_t attrKeyLen = 0;
                size_t attrValLen = 0;
                const lxb_char_t *key = lxb_dom_attr_qualified_name(attr, &attrKeyLen);
                const lxb_char_t *val = lxb_dom_attr_value(attr, &attrValLen);
                yyjson_mut_val *current_attr = yyjson_mut_obj(doc);

                yyjson_mut_obj_add_strn(doc, current_attr, "name", (const char *)key, attrKeyLen);
                yyjson_mut_obj_add_strn(doc, current_attr, "value", (const char *)val, attrValLen);
                yyjson_mut_arr_append(attributes, current_attr);

                attr = lxb_dom_element_next_attribute(attr);
            }
            yyjson_mut_obj_add_val(doc, obj,"attributes", attributes);

            lxb_tag_id_t tag_id = lxb_dom_node_tag_id(node);
            if (tag_id == LXB_TAG_TEMPLATE && node->ns == LXB_NS_HTML)
            {
                yyjson_mut_obj_add_bool(doc, obj, "isTemplate", true);
                lxb_dom_node_t *templateChild = lxb_html_interface_template(node)->content->node.first_child;
                while (templateChild) {
                    yyjson_mut_arr_add_val(children, node_to_json(templateChild, doc));
                    templateChild = lxb_dom_node_next(templateChild);
                }
            }
            else {
                yyjson_mut_obj_add_bool(doc, obj, "isTemplate", false);
            }
            lxb_dom_node_t *elChild = node->first_child;
            while (elChild) {
                yyjson_mut_arr_add_val(children, node_to_json(elChild, doc));
                elChild = lxb_dom_node_next(elChild);
            }

            yyjson_mut_obj_add_val(doc, obj, "childNodes", children);
            break;
        case LXB_DOM_NODE_TYPE_UNDEF:
        case LXB_DOM_NODE_TYPE_ATTRIBUTE:
        case LXB_DOM_NODE_TYPE_TEXT:
        case LXB_DOM_NODE_TYPE_CDATA_SECTION:
        case LXB_DOM_NODE_TYPE_ENTITY_REFERENCE:
        case LXB_DOM_NODE_TYPE_ENTITY:
        case LXB_DOM_NODE_TYPE_PROCESSING_INSTRUCTION:
        case LXB_DOM_NODE_TYPE_COMMENT:
            break;
        case LXB_DOM_NODE_TYPE_DOCUMENT:
            const lxb_dom_document_t *document = lxb_dom_interface_document(node);
            size_t documentLen = 0;
            const lxb_char_t *title = lxb_html_document_title((lxb_html_document_t *)lxb_dom_interface_node(node), &documentLen);
            yyjson_mut_obj_add_strn(doc, obj, "title", (const char *)title, documentLen);
            
            // Not implemented, here to match the PHP class LexborHtmlDocument
            yyjson_mut_obj_add_strn(doc, obj, "URL", "", 0);
            yyjson_mut_obj_add_strn(doc, obj, "documentURI", "", 0);
            yyjson_mut_obj_add_strn(doc, obj, "characterSet", "", 0);
            yyjson_mut_obj_add_strn(doc, obj, "charset", "", 0);
            yyjson_mut_obj_add_strn(doc, obj, "inputEncoding", "", 0);
            
            lxb_dom_node_t *documentChild = node->first_child;
            while (documentChild) {
                yyjson_mut_arr_add_val(children, node_to_json(documentChild, doc));
                documentChild = lxb_dom_node_next(documentChild);
            }
            yyjson_mut_obj_add_val(doc, obj, "childNodes", children);
            break;
        case LXB_DOM_NODE_TYPE_DOCUMENT_TYPE:
            const lxb_dom_document_type_t *documentType = lxb_dom_interface_document_type(node);
            //get name of the doctype
            const lxb_dom_attr_data_t *nameData = lxb_dom_attr_data_by_id(documentType->node.owner_document->attrs,
                                       documentType->name);
            const lxb_char_t *name = nameData->entry.u.short_str;
            const lxb_char_t *publicId = documentType->public_id.data;
            const lxb_char_t *systemId = documentType->system_id.data;
            
            yyjson_mut_obj_add_strn(doc, obj, "name", (const char *)name, nameData->entry.length);
            yyjson_mut_obj_add_strn(doc, obj, "publicId", (const char *)publicId, documentType->public_id.length);
            yyjson_mut_obj_add_strn(doc, obj, "systemId", (const char *)systemId, documentType->system_id.length);

            lxb_dom_node_t *documentTypeChild = node->first_child;
            while (documentTypeChild) {
                yyjson_mut_arr_add_val(children, node_to_json(documentTypeChild, doc));
                documentTypeChild = lxb_dom_node_next(documentTypeChild);
            }
            yyjson_mut_obj_add_val(doc, obj, "childNodes", children);
            break;
        case LXB_DOM_NODE_TYPE_DOCUMENT_FRAGMENT:
        case LXB_DOM_NODE_TYPE_NOTATION:
        case LXB_DOM_NODE_TYPE_CHARACTER_DATA:
        case LXB_DOM_NODE_TYPE_SHADOW_ROOT:
        case LXB_DOM_NODE_TYPE_LAST_ENTRY:
          break;
        }
    return obj;
}
// KNOWN BUGS

// Quand on passe <!---- >, il y a un souci avec les nodes childs, mais pas dans php/dom.

int main()
{
    size_t capacity = 65536;
    size_t len = 0;
    unsigned char *html = malloc(capacity);
    if (html == NULL)
    {
        printf("{}");
        return 0;
    }
    
    size_t n;
    while ((n = fread(html + len, 1, capacity - len, stdin)) > 0) {
        len += n;
        if (len == capacity) {
            capacity *= 2;
            html = realloc(html, capacity);
            if (html == NULL)
            {
                printf("{}");
                return 0;
            }
        }
    }
    
    // unsigned char html[] = "<!DOCTYPE html><table><template a=\"tetsmsuund 50\" ><a>a</a></template>";

    /* Create document */
    lxb_html_document_t *document = lxb_html_document_create();
    if (document == NULL) {
        free(html);
        return EXIT_FAILURE;
    }
    
    /* Parse HTML */
    lxb_status_t status = lxb_html_document_parse(document, html, len);
    free(html);

    if (status != LXB_STATUS_OK) {
        lxb_html_document_destroy(document);
        return EXIT_FAILURE;
    }

    yyjson_mut_doc *jsondoc = yyjson_mut_doc_new(NULL);
    yyjson_mut_val *root = NULL;
    lxb_dom_node_t *htmlDocument = lxb_dom_interface_node(document);
    // lxb_dom_node_t *child = lxb_dom_node_first_child(htmlDocument);
    while (htmlDocument)
    {
        root = node_to_json(htmlDocument, jsondoc);
        htmlDocument = lxb_dom_node_next(htmlDocument);
    }
    yyjson_mut_doc_set_root(jsondoc, root);
    const char *json = yyjson_mut_write(jsondoc, 0, NULL);
    printf("%s", json);
    fflush(NULL);
    lxb_html_document_destroy(document);

    return 0;
}
