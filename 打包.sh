#!/bin/sh

# 检查升级文件目录是否存在
if [ -d "升级文件" ]; then
    # 检查 update.zip 是否已经存在，如果存在则删除
    if [ -f "../update.zip" ]; then
        rm -f "../update.zip"
    fi
    # 进入升级文件所在的目录，假设该目录与脚本在同一目录下
    cd 升级文件
    # 使用 zip 命令将该目录下的所有文件打包成 update.zip
    zip -r ../update.zip *
    # 回到上一级目录
    cd ..
    # 输出打包完成的提示信息
    echo "打包完成"
else
    echo "升级文件目录不存在，请检查。"
fi