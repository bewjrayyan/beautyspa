@extends('errors.layout')

@section('code', (string) ($exception?->getStatusCode() ?? 500))
@section('eyebrow', trans('errors.500.eyebrow'))
@section('title', trans('errors.500.title'))
@section('message', trans('errors.500.message'))
