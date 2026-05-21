/**
* @author: claude code, not me.
*/

#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include "lexbor/html/parser.h"
#include "lexbor/html/serialize.h"
#include "lexbor/dom/interfaces/node.h"

/* Buffer de sortie global (simplifie la gestion mémoire JS) */
static char *g_output_buf = NULL;
static size_t g_output_len = 0;

/* Callback pour le sérialiseur Lexbor */
static lxb_status_t
serialize_callback(const lxb_char_t *data, size_t len, void *ctx)
{
    size_t new_len = g_output_len + len;
    char *new_buf = realloc(g_output_buf, new_len + 1);
    if (!new_buf) return LXB_STATUS_ERROR_MEMORY_ALLOCATION;
    
    memcpy(new_buf + g_output_len, data, len);
    new_buf[new_len] = '\0';
    g_output_buf = new_buf;
    g_output_len = new_len;
    return LXB_STATUS_OK;
}

/*
 * Fonction principale exposée à JS.
 * Prend une string HTML, retourne un pointeur vers la sérialisation HTML.
 * Le pointeur reste valide jusqu'au prochain appel.
 * JS doit lire la string AVANT le prochain appel.
 */
const char *
lexbor_parse_html(const char *html, size_t html_len)
{
    /* Reset du buffer de sortie */
    if (g_output_buf) {
        free(g_output_buf);
        g_output_buf = NULL;
        g_output_len = 0;
    }

    lxb_html_parser_t *parser = lxb_html_parser_create();
    if (!parser) return NULL;
    lxb_html_parser_init(parser);

    lxb_html_document_t *document = lxb_html_parse(
        parser,
        (const lxb_char_t *)html,
        html_len
    );

    if (!document) {
        lxb_html_parser_destroy(parser);
        return NULL;
    }
    printf("%d"; document.)
    /* Sérialiser le body en HTML */
    lxb_dom_node_t *body = lxb_dom_interface_node(
        lxb_html_document_body_element(document)
    );

    lxb_html_serialize_tree_cb(body, serialize_callback, NULL);

    lxb_html_document_destroy(document);
    lxb_html_parser_destroy(parser);

    return g_output_buf ? g_output_buf : "";
}

/* Libérer le buffer explicitement si nécessaire */
void
lexbor_free_result(void)
{
    if (g_output_buf) {
        free(g_output_buf);
        g_output_buf = NULL;
        g_output_len = 0;
    }
}
