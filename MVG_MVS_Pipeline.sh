#!/bin/bash
input_dir = $1
output_dir = $2

cd $output_dir

#openMVG

matches_dir=$output_dir/openMVG_result/matches
reconstruction_dir=$output_dir/openMVG_result/reconstruction_sequential
camera_file_params=/root/openMVG/src/openMVG/exif/sensor_width_database/sensor_width_camera_database.txt

mkdir openMVG_result && cd openMVG_result
mkdir matches && mkdir reconstruction_sequential

openMVG_main_SfMInit_ImageListing -i $input_dir -o $matches_dir -d $camera_file_params -k "2905.88;0;1416;0;2905.88;1064;0;0;1"

openMVG_main_ComputeFeatures -i $matches_dir/sfm_data.json -o $matches_dir -m "SIFT"

openMVG_main_ComputeMatches -i $matches_dir/sfm_data.json -o $matches_dir

openMVG_main_IncrementalSfM -i $matches_dir/sfm_data.json -m $matches_dir -o $reconstruction_dir

openMVG_main_ComputeSfM_DataColor -i $reconstruction_dir/sfm_data.bin -o $reconstruction_dir/colorized.ply

openMVG_main_ComputeStructureFromKnownPoses -i $reconstruction_dir/sfm_data.bin -m $matches_dir -f $matches_dir/matches.f.bin -o $reconstruction_dir/robust.bin

openMVG_main_ComputeSfM_DataColor -i $reconstruction_dir/robust.bin -o $reconstruction_dir/robust_colorized.ply

#openMVS

cd .. && mkdir openMVS_result && cd openMVS_result

openMVG_main_openMVG2openMVS -i $reconstruction_dir/sfm_data.bin -d $output_dir/openMVS_result -o $output_dir/openMVS_result/scene.mvs

DensifyPointCloud $output_dir/openMVS_result/scene.mvs

ReconstructMesh $output_dir/openMVS_result/scene_dense.mvs --export-type obj

#RefineMesh $output_dir/openMVS_result/scene_dense_mesh.mvs

TextureMesh $output_dir/openMVS_result/scene_dense_mesh.mvs --export-type obj


