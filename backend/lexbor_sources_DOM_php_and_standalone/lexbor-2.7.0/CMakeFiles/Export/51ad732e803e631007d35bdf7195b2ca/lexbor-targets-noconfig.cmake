#----------------------------------------------------------------
# Generated CMake target import file.
#----------------------------------------------------------------

# Commands may need to know the format version.
set(CMAKE_IMPORT_FILE_VERSION 1)

# Import target "lexbor::lexbor" for configuration ""
set_property(TARGET lexbor::lexbor APPEND PROPERTY IMPORTED_CONFIGURATIONS NOCONFIG)
set_target_properties(lexbor::lexbor PROPERTIES
  IMPORTED_LOCATION_NOCONFIG "${_IMPORT_PREFIX}/lib64/liblexbor.so.2.7.0"
  IMPORTED_SONAME_NOCONFIG "liblexbor.so.2"
  )

list(APPEND _cmake_import_check_targets lexbor::lexbor )
list(APPEND _cmake_import_check_files_for_lexbor::lexbor "${_IMPORT_PREFIX}/lib64/liblexbor.so.2.7.0" )

# Import target "lexbor::lexbor_static" for configuration ""
set_property(TARGET lexbor::lexbor_static APPEND PROPERTY IMPORTED_CONFIGURATIONS NOCONFIG)
set_target_properties(lexbor::lexbor_static PROPERTIES
  IMPORTED_LINK_INTERFACE_LANGUAGES_NOCONFIG "C"
  IMPORTED_LOCATION_NOCONFIG "${_IMPORT_PREFIX}/lib64/liblexbor_static.a"
  )

list(APPEND _cmake_import_check_targets lexbor::lexbor_static )
list(APPEND _cmake_import_check_files_for_lexbor::lexbor_static "${_IMPORT_PREFIX}/lib64/liblexbor_static.a" )

# Commands beyond this point should not need to know the version.
set(CMAKE_IMPORT_FILE_VERSION)
