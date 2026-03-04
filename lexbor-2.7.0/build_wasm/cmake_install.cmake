# Install script for directory: /home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0

# Set the install prefix
if(NOT DEFINED CMAKE_INSTALL_PREFIX)
  set(CMAKE_INSTALL_PREFIX "/home/kbtch_/emsdk/upstream/emscripten/cache/sysroot")
endif()
string(REGEX REPLACE "/$" "" CMAKE_INSTALL_PREFIX "${CMAKE_INSTALL_PREFIX}")

# Set the install configuration name.
if(NOT DEFINED CMAKE_INSTALL_CONFIG_NAME)
  if(BUILD_TYPE)
    string(REGEX REPLACE "^[^A-Za-z0-9_]+" ""
           CMAKE_INSTALL_CONFIG_NAME "${BUILD_TYPE}")
  else()
    set(CMAKE_INSTALL_CONFIG_NAME "Release")
  endif()
  message(STATUS "Install configuration: \"${CMAKE_INSTALL_CONFIG_NAME}\"")
endif()

# Set the component getting installed.
if(NOT CMAKE_INSTALL_COMPONENT)
  if(COMPONENT)
    message(STATUS "Install component: \"${COMPONENT}\"")
    set(CMAKE_INSTALL_COMPONENT "${COMPONENT}")
  else()
    set(CMAKE_INSTALL_COMPONENT)
  endif()
endif()

# Is this installation the result of a crosscompile?
if(NOT DEFINED CMAKE_CROSSCOMPILING)
  set(CMAKE_CROSSCOMPILING "TRUE")
endif()

# Set path to fallback-tool for dependency-resolution.
if(NOT DEFINED CMAKE_OBJDUMP)
  set(CMAKE_OBJDUMP "/usr/bin/objdump")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/core" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/at_rule" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/property" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/selectors" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/syntax" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/unit" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/css" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/css/value" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/dom" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/dom" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/dom/interfaces" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/encoding" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/engine" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/html" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/html" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/html/interfaces" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/html" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/html/tokenizer" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/html" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/html/tree" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/ns" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/punycode" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/selectors" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/style" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/style" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/style/dom" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor/style" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/style/html" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/tag" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/unicode" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/url" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/include/lexbor" TYPE DIRECTORY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/source/lexbor/utils" FILES_MATCHING REGEX "/[^/]*\\.h$")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/lib" TYPE STATIC_LIBRARY FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/liblexbor_static.a")
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  if(EXISTS "$ENV{DESTDIR}${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor/lexbor-targets.cmake")
    file(DIFFERENT _cmake_export_file_changed FILES
         "$ENV{DESTDIR}${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor/lexbor-targets.cmake"
         "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/CMakeFiles/Export/e1c32fe419c5cd94159d6d11fc3018ae/lexbor-targets.cmake")
    if(_cmake_export_file_changed)
      file(GLOB _cmake_old_config_files "$ENV{DESTDIR}${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor/lexbor-targets-*.cmake")
      if(_cmake_old_config_files)
        string(REPLACE ";" ", " _cmake_old_config_files_text "${_cmake_old_config_files}")
        message(STATUS "Old export file \"$ENV{DESTDIR}${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor/lexbor-targets.cmake\" will be replaced.  Removing files [${_cmake_old_config_files_text}].")
        unset(_cmake_old_config_files_text)
        file(REMOVE ${_cmake_old_config_files})
      endif()
      unset(_cmake_old_config_files)
    endif()
    unset(_cmake_export_file_changed)
  endif()
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor" TYPE FILE FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/CMakeFiles/Export/e1c32fe419c5cd94159d6d11fc3018ae/lexbor-targets.cmake")
  if(CMAKE_INSTALL_CONFIG_NAME MATCHES "^([Rr][Ee][Ll][Ee][Aa][Ss][Ee])$")
    file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor" TYPE FILE FILES "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/CMakeFiles/Export/e1c32fe419c5cd94159d6d11fc3018ae/lexbor-targets-release.cmake")
  endif()
endif()

if(CMAKE_INSTALL_COMPONENT STREQUAL "Unspecified" OR NOT CMAKE_INSTALL_COMPONENT)
  file(INSTALL DESTINATION "${CMAKE_INSTALL_PREFIX}/lib/cmake/lexbor" TYPE FILE FILES
    "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/lexbor-config.cmake"
    "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/lexbor-config-version.cmake"
    )
endif()

string(REPLACE ";" "\n" CMAKE_INSTALL_MANIFEST_CONTENT
       "${CMAKE_INSTALL_MANIFEST_FILES}")
if(CMAKE_INSTALL_LOCAL_ONLY)
  file(WRITE "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/install_local_manifest.txt"
     "${CMAKE_INSTALL_MANIFEST_CONTENT}")
endif()
if(CMAKE_INSTALL_COMPONENT)
  if(CMAKE_INSTALL_COMPONENT MATCHES "^[a-zA-Z0-9_.+-]+$")
    set(CMAKE_INSTALL_MANIFEST "install_manifest_${CMAKE_INSTALL_COMPONENT}.txt")
  else()
    string(MD5 CMAKE_INST_COMP_HASH "${CMAKE_INSTALL_COMPONENT}")
    set(CMAKE_INSTALL_MANIFEST "install_manifest_${CMAKE_INST_COMP_HASH}.txt")
    unset(CMAKE_INST_COMP_HASH)
  endif()
else()
  set(CMAKE_INSTALL_MANIFEST "install_manifest.txt")
endif()

if(NOT CMAKE_INSTALL_LOCAL_ONLY)
  file(WRITE "/home/kbtch_/Documents/MXSS_HtmlSanitizer_Symphony/lexbor-2.7.0/build_wasm/${CMAKE_INSTALL_MANIFEST}"
     "${CMAKE_INSTALL_MANIFEST_CONTENT}")
endif()
