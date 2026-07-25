@extends('errors.layout')

@section('code', (string) ($exception?->getStatusCode() ?? 400))
@section('eyebrow', trans('errors.generic.eyebrow'))
@section('title', trans('errors.generic.title'))
@section('message', trans('errors.generic.message'))
