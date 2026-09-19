# Dom-Explorer, Spaghetti Code Edition

This is a fork of the beautiful projet DOM-Explorer, made by Bitk from YesWeHack.

The purpose of this fork is to provide the hability for the web app to choose 3 new parser/sanitizer :

- PHP Dom Parser, which uses lexbor under the hood
- Lexbor 2.7 parser, in order to test differences between the PHP Dom library and lexbor in a standalone program.
- Symfony HTML Sanitizer, to test a php library that uses the PHP Dom library under the hood.

The pipeline is as follow (AI generated) :

<img width="1300" height="1120" alt="architecture drawio" src="https://github.com/user-attachments/assets/77d557a4-471e-449f-8fdf-e25b35dd4657" />


The idea was to test if a backend parser could be vulnerable to some form of HTML mutation. I wrote articles about this journey here : https://kibatche.github.io/.

To date, 3 articles are online, the last one will be hopefully released during the end of the year 2026.

# Dom-Explorer

Dom-Explorer is a web-based tool designed for testing various HTML parsers and sanitizers. It displays the results of each parser as a tree and allows users to create pipelines that chain multiple parsers to visualize the transformation of HTML at each step. 

This repository contains the source code for the website published at: [Dom-Explorer Website](https://yeswehack.github.io/Dom-Explorer/).

## Features

- **Parser/Sanitizer Support:**
  - [Ammonia](https://github.com/rust-ammonia/ammonia)
  - [Angular](https://angular.io/)
  - [DomParser](https://developer.mozilla.org/en-US/docs/Web/API/DOMParser)
  - [DomPurify](https://github.com/cure53/DOMPurify)
  - [HighlightJs](https://highlightjs.org/)
  - [JsXss](https://jsxss.com/en/index.html)
  - [Parse5](https://github.com/inikulin/parse5)
  - [SafeValues](https://github.com/google/safevalues)
  - [SrcdocParser](https://developer.mozilla.org/en-US/docs/Web/API/HTMLIFrameElement/srcdoc)
  - [TemplateParser](https://developer.mozilla.org/en-US/docs/Web/API/HTMLTemplateElement)

- **Tab Sync:**  
  Synchronizes pipelines and HTML parsing across multiple browser tabs in real-time.

- **Embeddable Pipelines:**  
  Users can embed pipelines into their websites as an iframe for others to interact with.

- **Presets:**  
  Save commonly used pipelines for easy reuse.

- **Shareable URL:**  
  Share pipelines by simply copying and pasting the URL. The state of the pipeline is embedded in the URL, making it easy to share with others.

## Getting Started

To get started with Dom-Explorer:

1. Clone the project and install dependencies:
   ```bash
   git clone https://github.com/yeswehack/Dom-Explorer
   cd Dom-Explorer
   bun install
   ```

2. Run the development server:
   ```bash
   cd web
   bun run --bun dev
   ```

## Contributing

Contributions are welcome! Feel free to open issues or submit pull requests to improve this tool.
